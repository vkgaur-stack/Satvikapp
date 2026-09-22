<?php

namespace App\Libraries;

/**
 * The five out-of-the-box dashboards from the PRD. Each returns KPIs plus one detail table.
 * Row cells are raw values; `formats` tells the view how to display them (CSV export uses raw values).
 */
class Reports
{
    public const TABS = [
        'fundraising'        => 'Fundraising',
        'beneficiary-impact' => 'Beneficiary impact',
        'aid-distribution'   => 'Aid distribution',
        'field-operations'   => 'Field operations',
        'donor-engagement'   => 'Donor engagement',
    ];

    public static function build(string $slug): ?array
    {
        return match ($slug) {
            'fundraising'        => self::fundraising(),
            'beneficiary-impact' => self::impact(),
            'aid-distribution'   => self::aid(),
            'field-operations'   => self::field(),
            'donor-engagement'   => self::engagement(),
            default              => null,
        };
    }

    private static function one(string $sql, array $bind = []): array
    {
        return db_connect()->query($sql, $bind)->getRowArray() ?: [];
    }

    private static function fundraising(): array
    {
        $fy   = fy_bounds();
        $raised = self::one("SELECT COALESCE(SUM(amount),0) s, COUNT(*) n FROM donations WHERE payment_status='captured' AND deleted_at IS NULL AND donated_on >= ?", [$fy['start']]);
        $ret  = self::one("SELECT COUNT(DISTINCT p.donor_id) prev, COUNT(DISTINCT c.donor_id) kept FROM donations p
                           LEFT JOIN donations c ON c.donor_id=p.donor_id AND c.donated_on >= ? AND c.payment_status='captured' AND c.deleted_at IS NULL
                           WHERE p.donated_on >= ? AND p.donated_on < ? AND p.payment_status='captured' AND p.deleted_at IS NULL", [$fy['start'], $fy['prev'], $fy['start']]);
        $rec  = self::one("SELECT COUNT(DISTINCT donor_id) n FROM donations WHERE frequency <> 'one_off' AND payment_status='captured' AND deleted_at IS NULL AND donated_on >= ?", [$fy['start']]);
        $givers = self::one("SELECT COUNT(DISTINCT donor_id) n FROM donations WHERE payment_status='captured' AND deleted_at IS NULL AND donated_on >= ?", [$fy['start']]);
        $lapsed = self::one("SELECT COUNT(*) n FROM donors WHERE status='lapsed' AND deleted_at IS NULL");
        $rows = db_connect()->query("SELECT c.name, c.target_goal, COALESCE(SUM(CASE WHEN d.payment_status='captured' AND d.deleted_at IS NULL THEN d.amount END),0) raised,
                COUNT(CASE WHEN d.payment_status='captured' AND d.deleted_at IS NULL THEN 1 END) gifts
              FROM campaigns c LEFT JOIN donations d ON d.campaign_id=c.id WHERE c.deleted_at IS NULL GROUP BY c.id, c.name, c.target_goal ORDER BY raised DESC")->getResultArray();

        return [
            'title' => 'Fundraising', 'description' => 'Financial year ' . $fy['label'] . ' (April to March).',
            'kpis' => [
                ['Raised this year', $raised['s'], 'money'],
                ['Gifts received', $raised['n'], 'int'],
                ['Average gift', $raised['n'] ? $raised['s'] / $raised['n'] : 0, 'money'],
                ['Donor retention', $ret['prev'] ? round(100 * $ret['kept'] / $ret['prev'], 1) : null, 'pct'],
                ['Recurring givers', $givers['n'] ? round(100 * $rec['n'] / $givers['n'], 1) : null, 'pct'],
                ['Lapsed donors (6+ months)', $lapsed['n'], 'int'],
            ],
            'table_title' => 'Campaign performance vs goal',
            'headers' => ['Campaign', 'Goal', 'Raised', '% of goal', 'Gifts'],
            'formats' => ['text', 'money', 'money', 'pct', 'int'],
            'rows' => array_map(static fn ($r) => [$r['name'], $r['target_goal'], $r['raised'], $r['target_goal'] > 0 ? round(100 * $r['raised'] / $r['target_goal'], 1) : null, $r['gifts']], $rows),
        ];
    }

    private static function impact(): array
    {
        $tot  = self::one("SELECT COUNT(*) n FROM beneficiaries WHERE deleted_at IS NULL");
        $srv  = self::one("SELECT COUNT(DISTINCT beneficiary_id) n FROM enrollments WHERE status IN ('enrolled','completed') AND deleted_at IS NULL");
        $stat = self::one("SELECT SUM(status='completed') c, SUM(status='dropped') d, SUM(status='enrolled') e FROM enrollments WHERE deleted_at IS NULL");
        $inc  = self::one("SELECT AVG(e.monthly_income - b.monthly_income) v, COUNT(*) n FROM surveys b
                           JOIN surveys e ON e.beneficiary_id=b.beneficiary_id AND e.survey_type='endline' AND e.deleted_at IS NULL AND e.monthly_income IS NOT NULL
                           WHERE b.survey_type='baseline' AND b.deleted_at IS NULL AND b.monthly_income IS NOT NULL");
        $gr   = self::one("SELECT SUM(status='open') o, SUM(status='resolved') r FROM case_notes WHERE note_type='grievance' AND deleted_at IS NULL");
        $all  = (int) $stat['c'] + (int) $stat['d'] + (int) $stat['e'];
        $rows = db_connect()->query("SELECT p.name, p.category, p.target_beneficiaries seats,
                SUM(e.status='enrolled') enrolled, SUM(e.status='completed') completed, SUM(e.status='dropped') dropped
              FROM programs p LEFT JOIN enrollments e ON e.program_id=p.id AND e.deleted_at IS NULL WHERE p.deleted_at IS NULL GROUP BY p.id, p.name, p.category, p.target_beneficiaries ORDER BY p.name")->getResultArray();

        return [
            'title' => 'Beneficiary impact', 'description' => 'Reach, completion and household income change (baseline vs endline surveys).',
            'kpis' => [
                ['Beneficiaries registered', $tot['n'], 'int'],
                ['Currently served or completed', $srv['n'], 'int'],
                ['Program completion rate', $all ? round(100 * $stat['c'] / $all, 1) : null, 'pct'],
                ['Average income change (per month)', $inc['n'] ? $inc['v'] : null, 'money'],
                ['Open grievances', $gr['o'] ?? 0, 'int'],
                ['Resolved grievances', $gr['r'] ?? 0, 'int'],
            ],
            'table_title' => 'Program-wise enrolment',
            'headers' => ['Program', 'Area', 'Seats', 'Enrolled', 'Completed', 'Dropped', 'Dropout %'],
            'formats' => ['text', 'label', 'int', 'int', 'int', 'int', 'pct'],
            'rows' => array_map(static function ($r) {
                $base = (int) $r['enrolled'] + (int) $r['completed'] + (int) $r['dropped'];

                return [$r['name'], $r['category'], $r['seats'], (int) $r['enrolled'], (int) $r['completed'], (int) $r['dropped'], $base ? round(100 * $r['dropped'] / $base, 1) : null];
            }, $rows),
        ];
    }

    private static function aid(): array
    {
        $t = self::one("SELECT COUNT(*) n, COALESCE(SUM(CASE WHEN delivery_type='cash' THEN amount END),0) cash,
                        COALESCE(SUM(CASE WHEN delivery_type='in_kind' THEN amount END),0) kind,
                        SUM(status='pending') pending, SUM(status='verified') verified FROM aid_deliveries WHERE deleted_at IS NULL");
        $atRisk = self::one("SELECT COALESCE(SUM(amount),0) v FROM aid_deliveries WHERE status='pending' AND deleted_at IS NULL");
        $rows = db_connect()->query("SELECT p.name, COUNT(a.id) n, COALESCE(SUM(CASE WHEN a.delivery_type='cash' THEN a.amount END),0) cash,
                COALESCE(SUM(CASE WHEN a.delivery_type='in_kind' THEN a.amount END),0) kind, SUM(a.delivery_type='service') services, SUM(a.status='verified') verified
              FROM programs p LEFT JOIN aid_deliveries a ON a.program_id=p.id AND a.deleted_at IS NULL WHERE p.deleted_at IS NULL GROUP BY p.id, p.name ORDER BY p.name")->getResultArray();

        return [
            'title' => 'Aid distribution', 'description' => 'Cash, in-kind and service delivery across programs.',
            'kpis' => [
                ['Cash disbursed', $t['cash'], 'money'],
                ['In-kind value', $t['kind'], 'money'],
                ['Deliveries logged', $t['n'], 'int'],
                ['Verified deliveries', $t['n'] ? round(100 * $t['verified'] / $t['n'], 1) : null, 'pct'],
                ['Pending deliveries', $t['pending'] ?? 0, 'int'],
                ['Value pending delivery', $atRisk['v'], 'money'],
            ],
            'table_title' => 'By program',
            'headers' => ['Program', 'Deliveries', 'Cash', 'In-kind value', 'Services', 'Verified %'],
            'formats' => ['text', 'int', 'money', 'money', 'int', 'pct'],
            'rows' => array_map(static fn ($r) => [$r['name'], $r['n'], $r['cash'], $r['kind'], (int) $r['services'], $r['n'] ? round(100 * $r['verified'] / $r['n'], 1) : null], $rows),
        ];
    }

    private static function field(): array
    {
        $m30  = self::one("SELECT COUNT(*) n FROM beneficiaries WHERE deleted_at IS NULL AND created_at >= ?", [date('Y-m-d', strtotime('-30 days'))]);
        $dup  = self::one("SELECT COUNT(*) n FROM beneficiaries WHERE dedup_flag=1 AND deleted_at IS NULL");
        $docs = self::one("SELECT COUNT(*) n, SUM(verified_at IS NOT NULL) v FROM documents WHERE deleted_at IS NULL");
        $hh   = self::one("SELECT COUNT(DISTINCT beneficiary_id) n FROM households WHERE deleted_at IS NULL");
        $tot  = self::one("SELECT COUNT(*) n FROM beneficiaries WHERE deleted_at IS NULL");
        $noId = self::one("SELECT COUNT(*) n FROM beneficiaries WHERE id_type='none' AND deleted_at IS NULL");
        $rows = db_connect()->query("SELECT u.name, SUM(b.created_at >= ?) last30, COUNT(b.id) total, SUM(b.dedup_flag) flagged, MAX(b.created_at) last_reg
              FROM users u JOIN beneficiaries b ON b.created_by=u.id AND b.deleted_at IS NULL GROUP BY u.id, u.name ORDER BY total DESC", [date('Y-m-d', strtotime('-30 days'))])->getResultArray();

        return [
            'title' => 'Field operations', 'description' => 'Registration activity and data quality. (Offline-sync health is added with the offline release.)',
            'kpis' => [
                ['Registrations in last 30 days', $m30['n'], 'int'],
                ['Possible duplicates awaiting review', $dup['n'], 'int'],
                ['Profiles with household data', $tot['n'] ? round(100 * $hh['n'] / $tot['n'], 1) : null, 'pct'],
                ['Registered without an ID', $noId['n'], 'int'],
                ['Documents uploaded', $docs['n'], 'int'],
                ['Documents verified', $docs['n'] ? round(100 * $docs['v'] / $docs['n'], 1) : null, 'pct'],
            ],
            'table_title' => 'Registrations by field worker',
            'headers' => ['Worker', 'Last 30 days', 'Total', 'Duplicates flagged', 'Last registration'],
            'formats' => ['text', 'int', 'int', 'int', 'date'],
            'rows' => array_map(static fn ($r) => [$r['name'], (int) $r['last30'], (int) $r['total'], (int) $r['flagged'], $r['last_reg']], $rows),
        ];
    }

    private static function engagement(): array
    {
        $total = self::one("SELECT COALESCE(SUM(lifetime_value),0) s, COUNT(*) n FROM donors WHERE deleted_at IS NULL");
        $top10 = self::one("SELECT COALESCE(SUM(v),0) s FROM (SELECT lifetime_value v FROM donors WHERE deleted_at IS NULL ORDER BY lifetime_value DESC LIMIT 10) t");
        $reach = self::one("SELECT COUNT(*) n FROM donors WHERE communication_preference <> 'none' AND deleted_at IS NULL");
        $lapsed = self::one("SELECT COUNT(*) n FROM donors WHERE status='lapsed' AND deleted_at IS NULL");
        $sent  = self::one("SELECT COUNT(*) n FROM communications WHERE status='sent' AND created_at >= ?", [date('Y-m-d', strtotime('-30 days'))]);
        $recur = self::one("SELECT COUNT(DISTINCT donor_id) n FROM donations WHERE frequency <> 'one_off' AND payment_status='captured' AND deleted_at IS NULL");
        $rows = db_connect()->query("SELECT tier, COUNT(*) n, COALESCE(SUM(lifetime_value),0) s, COALESCE(AVG(lifetime_value),0) a FROM donors WHERE deleted_at IS NULL GROUP BY tier ORDER BY FIELD(tier,'major','gold','silver','bronze')")->getResultArray();

        return [
            'title' => 'Donor engagement', 'description' => 'Concentration, reachability and outreach activity.',
            'kpis' => [
                ['Top-10 donors share of giving', $total['s'] > 0 ? round(100 * $top10['s'] / $total['s'], 1) : null, 'pct'],
                ['Lapsed donors', $lapsed['n'], 'int'],
                ['Donors we can contact', $total['n'] ? round(100 * $reach['n'] / $total['n'], 1) : null, 'pct'],
                ['Messages sent (30 days)', $sent['n'], 'int'],
                ['Donors with recurring gifts', $recur['n'], 'int'],
                ['Total donors', $total['n'], 'int'],
            ],
            'table_title' => 'Donors by tier',
            'headers' => ['Tier', 'Donors', 'Total given', 'Average lifetime value'],
            'formats' => ['label', 'int', 'money', 'money'],
            'rows' => array_map(static fn ($r) => [$r['tier'], $r['n'], $r['s'], $r['a']], $rows),
        ];
    }
}
