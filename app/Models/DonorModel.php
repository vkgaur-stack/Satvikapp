<?php

namespace App\Models;

class DonorModel extends BaseModel
{
    protected $table         = 'donors';
    protected $allowedFields = ['name', 'email', 'phone', 'address', 'city', 'state', 'pan', 'tier', 'status', 'communication_preference', 'lifetime_value', 'last_gift_date', 'notes', 'created_by'];

    /**
     * Recalculate lifetime value, last gift, tier and active/lapsed status from captured donations.
     * Tier: Bronze < 10k, Silver 10k+, Gold 50k+, Major 2L+ (INR). Lapsed = no gift in 6 months.
     */
    public function refreshStats(int $donorId): void
    {
        $db  = db_connect();
        $row = $db->table('donations')
            ->select('COALESCE(SUM(amount),0) AS total, MAX(donated_on) AS last_gift', false)
            ->where('donor_id', $donorId)->where('payment_status', 'captured')->where('deleted_at', null)
            ->get()->getRowArray();

        $total = (float) ($row['total'] ?? 0);
        $last  = $row['last_gift'] ?? null;
        $tier  = $total >= 200000 ? 'major' : ($total >= 50000 ? 'gold' : ($total >= 10000 ? 'silver' : 'bronze'));

        $current = $db->table('donors')->select('status')->where('id', $donorId)->get()->getRowArray();
        if (! $current) {
            return;
        }
        $status = $current['status'];
        if ($status !== 'inactive') {
            $status = ($last === null || $last < date('Y-m-d', strtotime('-6 months'))) ? 'lapsed' : 'active';
        }

        $db->table('donors')->where('id', $donorId)->update([
            'lifetime_value' => $total,
            'last_gift_date' => $last,
            'tier'           => $tier,
            'status'         => $status,
        ]);
    }
}
