<?php

namespace App\Controllers;

use App\Libraries\ReceiptService;
use App\Models\DonationModel;
use Razorpay\Api\Api;

/**
 * Public online giving with Razorpay.
 *   GET  /donate            donation form (Razorpay Checkout in the browser)
 *   POST /donate/order      creates the Razorpay order server-side
 *   POST /donate/verify     verifies the payment signature, then issues + emails the receipt
 *   POST /razorpay/webhook  server-to-server confirmation (payment.captured / payment.failed)
 *
 * RAZORPAY_KEY_SECRET is read only here, on the server. The browser only ever receives the public key id.
 */
class Payments extends BaseController
{
    public function form()
    {
        $campaigns = db_connect()->table('campaigns')->select('id, name')->where('deleted_at', null)
            ->groupStart()->where('end_date IS NULL', null, false)->orWhere('end_date >=', date('Y-m-d'))->groupEnd()->orderBy('name')->get()->getResultArray();

        return view('donate/index', ['campaigns' => $campaigns, 'configured' => $this->configured()]);
    }

    public function order()
    {
        if (service('throttler')->check('donate_' . md5($this->request->getIPAddress()), 10, MINUTE) === false) {
            return $this->fail('Too many attempts. Please wait a minute.', 429);
        }
        if (! $this->configured()) {
            return $this->fail('Online giving is not switched on yet.', 503);
        }
        $in = $this->request->getJSON(true) ?? [];
        $v  = service('validation');
        $v->setRules([
            'name'        => 'required|min_length[2]|max_length[150]',
            'email'       => 'required|valid_email|max_length[150]',
            'phone'       => 'permit_empty|max_length[20]',
            'pan'         => 'permit_empty|regex_match[/^[A-Z]{5}[0-9]{4}[A-Z]$/]',
            'amount'      => 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[500000]',
            'campaign_id' => 'permit_empty|is_natural_no_zero|is_not_unique[campaigns.id]',
        ]);
        if (! $v->run($in)) {
            return $this->fail(implode(' ', $v->getErrors()), 422);
        }

        $db    = db_connect();
        $email = strtolower(trim($in['email']));
        $donor = $db->table('donors')->where('email', $email)->where('deleted_at', null)->get()->getRowArray();
        if (! $donor) {
            $db->table('donors')->insert([
                'name' => trim($in['name']), 'email' => $email, 'phone' => $in['phone'] ?: null, 'pan' => $in['pan'] ?: null,
                'communication_preference' => 'email', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $donorId = (int) $db->insertID();
        } else {
            $donorId = (int) $donor['id'];
            if (! empty($in['pan']) && empty($donor['pan'])) {
                $db->table('donors')->where('id', $donorId)->update(['pan' => $in['pan']]);
            }
        }

        $amount = round((float) $in['amount'], 2);
        try {
            $api   = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'));
            $order = $api->order->create([
                'receipt'  => 'don_' . $donorId . '_' . time(),
                'amount'   => (int) round($amount * 100),   // Razorpay wants paise (integer)
                'currency' => 'INR',
                'notes'    => ['donor_id' => (string) $donorId],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Razorpay order failed: ' . $e->getMessage());

            return $this->fail('Could not start the payment. Please try again.', 502);
        }

        (new DonationModel())->insert([
            'donor_id' => $donorId, 'campaign_id' => $in['campaign_id'] ?: null, 'amount' => $amount, 'currency' => 'INR',
            'donated_on' => date('Y-m-d'), 'payment_method' => 'online', 'payment_status' => 'pending', 'frequency' => 'one_off',
            'razorpay_order_id' => $order['id'],
        ]);

        return $this->response->setJSON([
            'key' => env('RAZORPAY_KEY_ID'), 'order_id' => $order['id'], 'amount' => (int) round($amount * 100), 'currency' => 'INR',
            'org' => org('name'), 'name' => trim($in['name']), 'email' => $email, 'contact' => (string) ($in['phone'] ?? ''),
        ]);
    }

    public function verify()
    {
        $in = $this->request->getJSON(true) ?? [];
        foreach (['razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature'] as $k) {
            if (empty($in[$k]) || ! is_string($in[$k])) {
                return $this->fail('Invalid payment response.', 422);
            }
        }
        try {
            (new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET')))->utility->verifyPaymentSignature([
                'razorpay_order_id' => $in['razorpay_order_id'], 'razorpay_payment_id' => $in['razorpay_payment_id'], 'razorpay_signature' => $in['razorpay_signature'],
            ]);
        } catch (\Throwable $e) {
            log_message('warning', 'Razorpay signature mismatch for order ' . $in['razorpay_order_id']);

            return $this->fail('Payment could not be verified.', 400);
        }
        $id = $this->capture($in['razorpay_order_id'], $in['razorpay_payment_id']);

        return $id ? $this->response->setJSON(['ok' => true]) : $this->fail('Unknown order.', 404);
    }

    public function webhook()
    {
        $secret = (string) env('RAZORPAY_WEBHOOK_SECRET', '');
        $body   = (string) $this->request->getBody();
        $sig    = $this->request->getHeaderLine('X-Razorpay-Signature');
        if ($secret === '' || $sig === '') {
            return $this->fail('Not configured.', 400);
        }
        try {
            (new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET')))->utility->verifyWebhookSignature($body, $sig, $secret);
        } catch (\Throwable $e) {
            return $this->fail('Bad signature.', 400);
        }
        $event  = json_decode($body, true) ?: [];
        $entity = $event['payload']['payment']['entity'] ?? [];
        if (($event['event'] ?? '') === 'payment.captured' && ! empty($entity['order_id'])) {
            $this->capture($entity['order_id'], $entity['id'] ?? null, (int) ($entity['amount'] ?? 0));
        } elseif (($event['event'] ?? '') === 'payment.failed' && ! empty($entity['order_id'])) {
            db_connect()->table('donations')->where('razorpay_order_id', $entity['order_id'])->where('payment_status', 'pending')->update(['payment_status' => 'failed']);
        }

        return $this->response->setJSON(['ok' => true]);
    }

    /** Mark the donation captured (idempotent), refresh donor stats, issue and email the receipt. */
    private function capture(string $orderId, ?string $paymentId, ?int $paise = null): ?int
    {
        $db = db_connect();
        $d  = $db->table('donations')->where('razorpay_order_id', $orderId)->where('deleted_at', null)->get()->getRowArray();
        if (! $d) {
            return null;
        }
        if ($paise && $paise !== (int) round($d['amount'] * 100)) {
            log_message('error', "Razorpay amount mismatch on order {$orderId}");

            return null;
        }
        if ($d['payment_status'] !== 'captured') {
            (new DonationModel())->update((int) $d['id'], ['payment_status' => 'captured', 'razorpay_payment_id' => $paymentId]);
            if (ReceiptService::issue((int) $d['id'])) {
                ReceiptService::email((int) $d['id']);
            }
        }

        return (int) $d['id'];
    }

    private function configured(): bool
    {
        return env('RAZORPAY_KEY_ID', '') !== '' && env('RAZORPAY_KEY_SECRET', '') !== '';
    }

    private function fail(string $msg, int $code)
    {
        return $this->response->setStatusCode($code)->setJSON(['error' => $msg]);
    }
}
