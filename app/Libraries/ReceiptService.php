<?php

namespace App\Libraries;

/** Tax-receipt (80G acknowledgement) helpers shared by the staff screens and the online-giving flow. */
class ReceiptService
{
    public static function load(int $donationId): ?array
    {
        $d = db_connect()->table('donations d')
            ->select('d.*, dn.name AS donor_name, dn.email AS donor_email, dn.phone AS donor_phone, dn.address AS donor_address, dn.city AS donor_city, dn.state AS donor_state, dn.pan AS donor_pan, c.name AS campaign_name')
            ->join('donors dn', 'dn.id = d.donor_id')->join('campaigns c', 'c.id = d.campaign_id', 'left')
            ->where('d.id', $donationId)->where('d.deleted_at', null)->get()->getRowArray();

        return $d ?: null;
    }

    /** Assign a receipt number (once) and mark the receipt as issued. Only captured payments qualify. */
    public static function issue(int $donationId): bool
    {
        $d = self::load($donationId);
        if (! $d || $d['payment_status'] !== 'captured') {
            return false;
        }
        if ($d['receipt_status'] === 'issued' && $d['receipt_no']) {
            return true;
        }
        $fy = fy_bounds($d['donated_on'])['label'];
        db_connect()->table('donations')->where('id', $donationId)->update([
            'receipt_status'    => 'issued',
            'receipt_no'        => sprintf('SD/%s/%06d', $fy, $donationId),
            'receipt_issued_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    /** E-mail the receipt and thank-you letter. @return string|null error message or null on success */
    public static function email(int $donationId): ?string
    {
        $d = self::load($donationId);
        if (! $d) {
            return 'Donation not found.';
        }
        if (empty($d['donor_email'])) {
            return 'This donor has no email address.';
        }
        if ($d['receipt_status'] !== 'issued') {
            return 'Issue the receipt first.';
        }
        $subject = 'Thank you - your donation receipt ' . $d['receipt_no'];
        $err     = Mailer::send($d['donor_email'], $subject, view('receipts/email', ['d' => $d]));
        db_connect()->table('communications')->insert([
            'donor_id' => $d['donor_id'], 'channel' => 'email', 'subject' => $subject, 'body' => 'Receipt ' . $d['receipt_no'],
            'status' => $err ? 'failed' : 'sent', 'error' => $err, 'created_by' => CurrentUser::id(), 'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $err;
    }
}
