<?php

namespace App\Libraries;

/** Numbers for the dashboard (web widgets and GET /api/v1/dashboard). Sections are gated by permission. */
class Metrics
{
    public static function dashboard(): array
    {
        $db = db_connect();
        $fy = fy_bounds();
        $out = ['fy_label' => $fy['label'], 'generated_at' => date('c')];

        if (can('dashboard.fundraising')) {
            $cap = static fn () => $db->table('donations')->where('payment_status', 'captured')->where('deleted_at', null);

            $raised = (float) ($cap()->selectSum('amount', 's')->where('donated_on >=', $fy['start'])->get()->getRow()->s ?? 0);
            $gifts  = $cap()->where('donated_on >=', $fy['start'])->countAllResults();

            $ret = $db->query(
                "SELECT COUNT(DISTINCT p.donor_id) AS prev, COUNT(DISTINCT c.donor_id) AS kept
                 FROM donations p
                 LEFT JOIN donations c ON c.donor_id = p.donor_id AND c.donated_on >= ? AND c.payment_status = 'captured' AND c.deleted_at IS NULL
                 WHERE p.donated_on >= ? AND p.donated_on < ? AND p.payment_status = 'captured' AND p.deleted_at IS NULL",
                [$fy['start'], $fy['prev'], $fy['start']]
            )->getRowArray();

            $series = [];
            for ($i = 5; $i >= 0; $i--) {
                $series[date('Y-m', strtotime("first day of -$i month"))] = 0.0;
            }
            $rows = $db->query(
                "SELECT DATE_FORMAT(donated_on, '%Y-%m') AS m, SUM(amount) AS s FROM donations
                 WHERE payment_status='captured' AND deleted_at IS NULL AND donated_on >= ? GROUP BY m",
                [date('Y-m-01', strtotime('-5 months'))]
            )->getResultArray();
            foreach ($rows as $r) {
                if (isset($series[$r['m']])) {
                    $series[$r['m']] = (float) $r['s'];
                }
            }

            $campaigns = $db->query(
                "SELECT c.id, c.name, c.target_goal,
                        COALESCE(SUM(CASE WHEN d.payment_status='captured' AND d.deleted_at IS NULL THEN d.amount END),0) AS raised
                 FROM campaigns c LEFT JOIN donations d ON d.campaign_id = c.id
                 WHERE c.deleted_at IS NULL GROUP BY c.id, c.name, c.target_goal ORDER BY c.id DESC LIMIT 6"
            )->getResultArray();

            $out['fundraising'] = [
                'raised_fy'       => $raised,
                'active_donors'   => $db->table('donors')->where('status', 'active')->where('deleted_at', null)->countAllResults(),
                'lapsed_donors'   => $db->table('donors')->where('status', 'lapsed')->where('deleted_at', null)->countAllResults(),
                'retention_rate'  => ((int) $ret['prev']) > 0 ? round(100 * $ret['kept'] / $ret['prev'], 1) : null,
                'average_gift'    => $gifts ? round($raised / $gifts, 2) : 0,
                'gifts_fy'        => $gifts,
                'monthly'         => array_map(static fn ($m, $v) => ['month' => date('M', strtotime($m . '-01')), 'amount' => $v], array_keys($series), array_values($series)),
                'campaigns'       => array_map(static fn ($c) => [
                    'id' => (int) $c['id'], 'name' => $c['name'], 'goal' => (float) $c['target_goal'], 'raised' => (float) $c['raised'],
                    'percent' => (float) $c['target_goal'] > 0 ? round(100 * $c['raised'] / $c['target_goal'], 1) : null,
                ], $campaigns),
            ];
        }

        if (can('donations.view')) {
            $recent = $db->table('donations d')
                ->select('d.id, d.amount, d.donated_on, d.payment_method, d.payment_status, d.receipt_status, dn.id AS donor_id, dn.name AS donor_name, c.name AS campaign')
                ->join('donors dn', 'dn.id = d.donor_id')->join('campaigns c', 'c.id = d.campaign_id', 'left')
                ->where('d.deleted_at', null)->orderBy('d.donated_on', 'DESC')->orderBy('d.id', 'DESC')->limit(8)
                ->get()->getResultArray();
            $showNames = can('identity.view');
            $out['recent_donations'] = array_map(static fn ($r) => [
                'id' => (int) $r['id'], 'amount' => (float) $r['amount'], 'date' => $r['donated_on'], 'method' => $r['payment_method'],
                'payment_status' => $r['payment_status'], 'receipt_status' => $r['receipt_status'], 'campaign' => $r['campaign'],
                'donor' => $showNames ? $r['donor_name'] : 'Donor #' . $r['donor_id'],
            ], $recent);
        }

        if (can('dashboard.impact')) {
            $aid = (float) ($db->table('aid_deliveries')->selectSum('amount', 's')->whereIn('delivery_type', ['cash', 'in_kind'])->where('delivered_on >=', $fy['start'])->where('deleted_at', null)->get()->getRow()->s ?? 0);
            $out['impact'] = [
                'beneficiaries'   => $db->table('beneficiaries')->where('deleted_at', null)->countAllResults(),
                'enrolled_now'    => $db->table('enrollments')->where('status', 'enrolled')->where('deleted_at', null)->countAllResults(),
                'programs_active' => $db->table('programs')->where('status', 'active')->where('deleted_at', null)->countAllResults(),
                'aid_fy'          => $aid,
                'open_grievances' => $db->table('case_notes')->where('note_type', 'grievance')->where('status', 'open')->where('deleted_at', null)->countAllResults(),
                'pending_review'  => $db->table('beneficiaries')->where('dedup_flag', 1)->where('deleted_at', null)->countAllResults(),
            ];
        }

        return $out;
    }
}
