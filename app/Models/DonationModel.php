<?php

namespace App\Models;

class DonationModel extends BaseModel
{
    protected $table         = 'donations';
    protected $allowedFields = [
        'donor_id', 'campaign_id', 'amount', 'currency', 'donated_on', 'payment_method', 'payment_status', 'frequency',
        'receipt_status', 'receipt_no', 'receipt_issued_at', 'reference_no', 'razorpay_order_id', 'razorpay_payment_id', 'notes', 'created_by',
    ];
    protected $afterInsert = ['syncDonor'];
    protected $afterUpdate = ['syncDonor'];
    protected $afterDelete = ['syncDonor'];

    /** Keep the donor's lifetime value / tier / status current after any change to a gift. */
    protected function syncDonor(array $data): array
    {
        $donorIds = [];
        if (isset($data['data']['donor_id'])) {
            $donorIds[] = (int) $data['data']['donor_id'];
        }
        foreach ((array) ($data['id'] ?? []) as $id) {
            $r = db_connect()->table('donations')->select('donor_id')->where('id', $id)->get()->getRowArray();
            if ($r) {
                $donorIds[] = (int) $r['donor_id'];
            }
        }
        $donors = new DonorModel();
        foreach (array_unique($donorIds) as $donorId) {
            $donors->refreshStats($donorId);
        }

        return $data;
    }
}
