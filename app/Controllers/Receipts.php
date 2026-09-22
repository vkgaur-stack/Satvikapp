<?php

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\ReceiptService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Receipts extends BaseController
{
    private function guard(string $perm)
    {
        return can($perm) ? null : $this->response->setStatusCode(403)->setBody(view('errors/forbidden'));
    }

    public function show(int $id)
    {
        if ($r = $this->guard('donations.view')) {
            return $r;
        }
        $d = ReceiptService::load($id);
        if (! $d) {
            throw PageNotFoundException::forPageNotFound();
        }
        Audit::log('view', 'donations', $id);

        return view('receipts/show', ['d' => $d]);
    }

    public function issue(int $id)
    {
        if ($r = $this->guard('receipts.issue')) {
            return $r;
        }
        if (! ReceiptService::issue($id)) {
            return redirect()->back()->with('error', 'A receipt can only be issued for a captured payment.');
        }
        Audit::log('receipt_issue', 'donations', $id);

        return redirect()->to(site_url('receipts/' . $id))->with('success', 'Receipt issued.');
    }

    public function email(int $id)
    {
        if ($r = $this->guard('receipts.issue')) {
            return $r;
        }
        $err = ReceiptService::email($id);
        if ($err) {
            return redirect()->back()->with('error', $err);
        }
        Audit::log('receipt_email', 'donations', $id);

        return redirect()->back()->with('success', 'Receipt emailed to the donor.');
    }
}
