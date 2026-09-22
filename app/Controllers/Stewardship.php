<?php

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\CurrentUser;
use App\Libraries\DraftWriter;
use App\Libraries\Mailer;
use App\Libraries\WhatsApp;

/** Lapsed-donor outreach: find donors quiet for 6+ months, draft a personal message, review, send. */
class Stewardship extends BaseController
{
    private function guard()
    {
        return can('stewardship.manage') ? null : $this->response->setStatusCode(403)->setBody(view('errors/forbidden'));
    }

    public function index()
    {
        if ($r = $this->guard()) {
            return $r;
        }
        $cut    = date('Y-m-d', strtotime('-6 months'));
        $donors = db_connect()->table('donors')
            ->select('id, name, email, phone, tier, lifetime_value, last_gift_date, communication_preference')
            ->where('deleted_at', null)->where('status !=', 'inactive')->where('last_gift_date <', $cut)
            ->orderBy('lifetime_value', 'DESC')->get()->getResultArray();

        return view('stewardship/index', [
            'donors'   => $donors,
            'whatsapp' => WhatsApp::configured(),
            'ai'       => env('ANTHROPIC_API_KEY', '') !== '',
        ]);
    }

    public function draft()
    {
        if ($this->guard()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }
        $donor = $this->donor((int) ($this->request->getJSON(true)['donor_id'] ?? 0));
        if (! $donor) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Donor not found']);
        }
        $gifts = db_connect()->table('donations d')->select('d.amount, d.donated_on, c.name AS campaign')
            ->join('campaigns c', 'c.id = d.campaign_id', 'left')
            ->where('d.donor_id', $donor['id'])->where('d.payment_status', 'captured')->where('d.deleted_at', null)
            ->orderBy('d.donated_on', 'DESC')->limit(5)->get()->getResultArray();

        return $this->response->setJSON(DraftWriter::write($donor, $gifts));
    }

    public function send()
    {
        if ($this->guard()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }
        $in      = $this->request->getJSON(true) ?? [];
        $donor   = $this->donor((int) ($in['donor_id'] ?? 0));
        $channel = $in['channel'] ?? '';
        $subject = trim((string) ($in['subject'] ?? ''));
        $body    = trim((string) ($in['body'] ?? ''));
        if (! $donor || ! in_array($channel, ['email', 'whatsapp'], true) || $body === '' || mb_strlen($body) > 4000 || ($channel === 'email' && $subject === '')) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'error' => 'Please complete the message.']);
        }
        $pref = $donor['communication_preference'];
        if ($pref === 'none' || ($channel === 'email' && $pref === 'whatsapp') || ($channel === 'whatsapp' && $pref === 'email')) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'error' => 'This donor has asked not to be contacted on that channel.']);
        }

        if ($channel === 'email') {
            $err = empty($donor['email']) ? 'No email address on file.' : Mailer::send($donor['email'], $subject, '<div style="font-family:Arial,sans-serif;line-height:1.55">' . nl2br(esc($body)) . '</div>');
        } else {
            $err = empty($donor['phone']) ? 'No phone number on file.' : WhatsApp::sendText($donor['phone'], $body);
        }
        db_connect()->table('communications')->insert([
            'donor_id' => $donor['id'], 'channel' => $channel, 'subject' => $subject ?: null, 'body' => $body,
            'status' => $err ? 'failed' : 'sent', 'error' => $err, 'created_by' => CurrentUser::id(), 'created_at' => date('Y-m-d H:i:s'),
        ]);
        Audit::log('send_' . $channel, 'donors', (int) $donor['id']);

        return $err ? $this->response->setStatusCode(502)->setJSON(['ok' => false, 'error' => $err]) : $this->response->setJSON(['ok' => true]);
    }

    private function donor(int $id): ?array
    {
        return db_connect()->table('donors')->where('id', $id)->where('deleted_at', null)->get()->getRowArray() ?: null;
    }
}
