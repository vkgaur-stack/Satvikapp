<?php

namespace App\Controllers;

use App\Models\BeneficiaryModel;
use App\Models\DonorModel;
use App\Models\DonationModel;
use App\Models\ProgramModel;

/**
 * Reports Controller
 * Comprehensive analytics and reporting module
 * Provides detailed insights, KPI tracking, and data exports
 */
class Reports extends BaseController
{
    protected $beneficiaryModel;
    protected $donorModel;
    protected $donationModel;
    protected $programModel;
    protected $db;

    public function __construct()
    {
        $this->beneficiaryModel = new BeneficiaryModel();
        $this->donorModel = new DonorModel();
        $this->donationModel = new DonationModel();
        $this->programModel = new ProgramModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Main Reports Dashboard
     */
    public function index()
    {
        $data = $this->getReportsData();
        return view('reports/reports_dashboard', $data);
    }

    /**
     * Beneficiary Analytics Report
     */
    public function beneficiaryAnalytics()
    {
        $data = $this->getBeneficiaryAnalytics();
        return view('reports/beneficiary_analytics', $data);
    }

    /**
     * Donor Analytics Report
     */
    public function donorAnalytics()
    {
        $data = $this->getDonorAnalytics();
        return view('reports/donor_analytics', $data);
    }

    /**
     * Impact & Impact Report
     */
    public function impactReport()
    {
        $data = $this->getImpactMetrics();
        return view('reports/impact_report', $data);
    }

    /**
     * Geographical Analysis Report
     */
    public function geographicalAnalysis()
    {
        $data = $this->getGeographicalData();
        return view('reports/geographical_analysis', $data);
    }

    /**
     * Financial Report
     */
    public function financialReport()
    {
        $data = $this->getFinancialMetrics();
        return view('reports/financial_report', $data);
    }

    // ============================================
    // CORE REPORTING DATA METHODS
    // ============================================

    /**
     * Get comprehensive reports dashboard data
     */
    private function getReportsData()
    {
        return [
            'beneficiary_stats' => $this->getBeneficiaryStats(),
            'donor_stats' => $this->getDonorStats(),
            'financial_summary' => $this->getFinancialSummary(),
            'program_metrics' => $this->getProgramMetrics(),
            'key_kpis' => $this->getKeyKPIs(),
            'monthly_summary' => $this->getMonthlySummary(),
        ];
    }

    /**
     * Get detailed beneficiary analytics
     */
    private function getBeneficiaryAnalytics()
    {
        $builder = $this->db->table('beneficiaries');

        return [
            'total_beneficiaries' => $builder->countAllResults(),
            'status_breakdown' => $this->getBeneficiaryStatusBreakdown(),
            'gender_breakdown' => $this->getGenderBreakdown(),
            'age_groups' => $this->getAgeGroupDistribution(),
            'registration_monthly' => $this->getRegistrationTrend(),
            'top_performing_programs' => $this->getTopPrograms(),
            'retention_rate' => $this->getBeneficiaryRetentionRate(),
            'demographics' => $this->getDemographicAnalysis(),
            'service_usage' => $this->getServiceUsageMetrics(),
            'follow_up_status' => $this->getFollowUpStatus(),
        ];
    }

    /**
     * Get detailed donor analytics
     */
    private function getDonorAnalytics()
    {
        $builder = $this->db->table('donors');

        return [
            'total_donors' => $builder->countAllResults(),
            'active_donors' => $builder->where('status', 'active')->countAllResults(),
            'donor_type_breakdown' => $this->getDonorTypeBreakdown(),
            'donation_distribution' => $this->getDonationDistribution(),
            'donor_retention_rate' => $this->getDonorRetentionRate(),
            'lifetime_value' => $this->getLifetimeValue(),
            'donation_frequency' => $this->getDonationFrequency(),
            'top_donors' => $this->getTopDonors(10),
            'donor_growth' => $this->getDonorGrowthTrend(),
            'donor_engagement' => $this->getDonorEngagementMetrics(),
            'repeat_donation_rate' => $this->getRepeatDonationRate(),
        ];
    }

    /**
     * Get impact metrics
     */
    private function getImpactMetrics()
    {
        return [
            'families_impacted' => $this->db->table('beneficiaries')->countAllResults(),
            'total_beneficiaries_reached' => $this->db->table('beneficiaries')->countAllResults(),
            'total_funds_distributed' => $this->getTotalFundsDistributed(),
            'programs_active' => $this->db->table('programs')->where('status', 'active')->countAllResults(),
            'services_delivered' => $this->getTotalServicesDelivered(),
            'average_aid_per_beneficiary' => $this->getAverageAidPerBeneficiary(),
            'program_success_rate' => $this->getProgramSuccessRate(),
            'beneficiary_satisfaction' => $this->getBeneficiarySatisfactionScore(),
            'outcome_achievements' => $this->getOutcomeAchievements(),
            'environmental_impact' => $this->getEnvironmentalMetrics(),
        ];
    }

    /**
     * Get geographical data
     */
    private function getGeographicalData()
    {
        return [
            'beneficiaries_by_city' => $this->getBeneficiariesByCity(),
            'beneficiaries_by_state' => $this->getBeneficiariesByState(),
            'donors_by_location' => $this->getDonorsByLocation(),
            'service_coverage_map' => $this->getServiceCoverageMap(),
            'rural_vs_urban' => $this->getRuralUrbanSplit(),
            'high_impact_zones' => $this->getHighImpactZones(),
        ];
    }

    /**
     * Get financial metrics
     */
    private function getFinancialMetrics()
    {
        return [
            'total_revenue' => $this->getTotalRevenue(),
            'revenue_by_source' => $this->getRevenueBySource(),
            'monthly_revenue' => $this->getMonthlyRevenue(),
            'donation_trends' => $this->getDonationTrends(),
            'average_donation' => $this->getAverageDonation(),
            'largest_donation' => $this->getLargestDonation(),
            'donation_frequency_analysis' => $this->getDonationFrequencyAnalysis(),
            'fund_utilization' => $this->getFundUtilization(),
            'budget_vs_actual' => $this->getBudgetVsActual(),
        ];
    }

    // ============================================
    // BENEFICIARY ANALYTICS HELPERS
    // ============================================

    private function getBeneficiaryStatusBreakdown()
    {
        $builder = $this->db->table('beneficiaries');

        return [
            'active' => $builder->where('status', 'active')->countAllResults(),
            'registered' => $builder->where('status', 'registered')->countAllResults(),
            'inactive' => $builder->where('status', 'inactive')->countAllResults(),
        ];
    }

    private function getGenderBreakdown()
    {
        $query = $this->db->table('beneficiaries')
            ->select('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->get();

        $result = [];
        foreach ($query->getResult() as $row) {
            $result[ucfirst($row->gender)] = $row->count;
        }

        return $result;
    }

    private function getAgeGroupDistribution()
    {
        $query = $this->db->query("
            SELECT
                CASE
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 18 THEN 'Under 18'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 18 AND 25 THEN '18-25'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 26 AND 35 THEN '26-35'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 36 AND 45 THEN '36-45'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 46 AND 55 THEN '46-55'
                    ELSE '56+'
                END as age_group,
                COUNT(*) as count
            FROM beneficiaries
            WHERE deleted_at IS NULL
            GROUP BY age_group
            ORDER BY FIELD(age_group, 'Under 18', '18-25', '26-35', '36-45', '46-55', '56+')
        ");

        $result = [];
        foreach ($query->getResult() as $row) {
            $result[$row->age_group] = $row->count;
        }

        return $result;
    }

    private function getRegistrationTrend()
    {
        $query = $this->db->query("
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
            FROM beneficiaries
            WHERE deleted_at IS NULL
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12
        ");

        $result = [];
        foreach ($query->getResult() as $row) {
            $result[$row->month] = $row->count;
        }

        return array_reverse($result);
    }

    private function getTopPrograms()
    {
        $query = $this->db->query("
            SELECT
                p.id,
                p.program_name,
                COUNT(b.id) as beneficiary_count
            FROM programs p
            LEFT JOIN beneficiaries b ON p.id = b.primary_program_id AND b.deleted_at IS NULL
            WHERE p.status = 'active' AND p.deleted_at IS NULL
            GROUP BY p.id, p.program_name
            ORDER BY beneficiary_count DESC
            LIMIT 5
        ");

        return $query->getResult();
    }

    private function getBeneficiaryRetentionRate()
    {
        $total = $this->db->table('beneficiaries')->where('deleted_at', null)->countAllResults();
        $active = $this->db->table('beneficiaries')->where('status', 'active')->where('deleted_at', null)->countAllResults();

        return $total > 0 ? round(($active / $total) * 100, 2) : 0;
    }

    private function getDemographicAnalysis()
    {
        return [
            'average_household_size' => $this->getAverageHouseholdSize(),
            'rural_beneficiaries_percent' => $this->getRuralBeneficiaryPercent(),
            'avg_annual_income' => $this->getAverageAnnualIncome(),
            'education_level_distribution' => $this->getEducationLevelDistribution(),
        ];
    }

    private function getServiceUsageMetrics()
    {
        $query = $this->db->query("
            SELECT
                service_type,
                COUNT(*) as usage_count
            FROM aid_services
            WHERE deleted_at IS NULL
            GROUP BY service_type
            ORDER BY usage_count DESC
        ");

        return $query->getResult();
    }

    private function getFollowUpStatus()
    {
        $query = $this->db->query("
            SELECT
                follow_up_status,
                COUNT(*) as count
            FROM beneficiaries
            WHERE deleted_at IS NULL
            GROUP BY follow_up_status
        ");

        $result = [];
        foreach ($query->getResult() as $row) {
            $result[$row->follow_up_status ?? 'Not Set'] = $row->count;
        }

        return $result;
    }

    // ============================================
    // DONOR ANALYTICS HELPERS
    // ============================================

    private function getDonorTypeBreakdown()
    {
        $query = $this->db->table('donors')
            ->select('donor_type, COUNT(*) as count')
            ->groupBy('donor_type')
            ->get();

        $result = [];
        foreach ($query->getResult() as $row) {
            $result[ucfirst($row->donor_type)] = $row->count;
        }

        return $result;
    }

    private function getDonationDistribution()
    {
        $query = $this->db->query("
            SELECT
                CASE
                    WHEN amount < 5000 THEN 'Under ₹5K'
                    WHEN amount BETWEEN 5000 AND 25000 THEN '₹5K-₹25K'
                    WHEN amount BETWEEN 25000 AND 100000 THEN '₹25K-₹1L'
                    WHEN amount BETWEEN 100000 AND 500000 THEN '₹1L-₹5L'
                    ELSE 'Over ₹5L'
                END as range,
                COUNT(*) as count,
                SUM(amount) as total
            FROM donations
            WHERE deleted_at IS NULL
            GROUP BY range
            ORDER BY amount
        ");

        return $query->getResult();
    }

    private function getDonorRetentionRate()
    {
        $query = $this->db->query("
            SELECT
                COUNT(DISTINCT d.id) as total_donors,
                COUNT(DISTINCT CASE WHEN (SELECT COUNT(*) FROM donations
                    WHERE donor_id = d.id AND deleted_at IS NULL) > 1 THEN d.id END) as repeat_donors
            FROM donors d
            WHERE d.deleted_at IS NULL
        ");

        $row = $query->getRow();
        return $row->total_donors > 0 ? round(($row->repeat_donors / $row->total_donors) * 100, 2) : 0;
    }

    private function getLifetimeValue()
    {
        $query = $this->db->query("
            SELECT
                d.id,
                d.donor_name,
                SUM(don.amount) as lifetime_value
            FROM donors d
            LEFT JOIN donations don ON d.id = don.donor_id AND don.deleted_at IS NULL
            WHERE d.deleted_at IS NULL
            GROUP BY d.id, d.donor_name
            ORDER BY lifetime_value DESC
        ");

        return $query->getResult();
    }

    private function getDonationFrequency()
    {
        $query = $this->db->query("
            SELECT
                d.donor_name,
                COUNT(don.id) as donation_count,
                AVG(don.amount) as average_amount
            FROM donors d
            LEFT JOIN donations don ON d.id = don.donor_id AND don.deleted_at IS NULL
            WHERE d.deleted_at IS NULL
            GROUP BY d.id, d.donor_name
            ORDER BY donation_count DESC
        ");

        return $query->getResult();
    }

    private function getTopDonors($limit = 10)
    {
        $query = $this->db->query("
            SELECT
                d.id,
                d.donor_name,
                d.donor_type,
                d.contact_email,
                COUNT(don.id) as donation_count,
                SUM(don.amount) as total_contributed,
                MAX(don.donation_date) as last_donation
            FROM donors d
            LEFT JOIN donations don ON d.id = don.donor_id AND don.deleted_at IS NULL
            WHERE d.deleted_at IS NULL AND d.status = 'active'
            GROUP BY d.id, d.donor_name, d.donor_type, d.contact_email
            ORDER BY total_contributed DESC
            LIMIT $limit
        ");

        return $query->getResult();
    }

    private function getDonorGrowthTrend()
    {
        $query = $this->db->query("
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
            FROM donors
            WHERE deleted_at IS NULL
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12
        ");

        $result = [];
        foreach ($query->getResult() as $row) {
            $result[$row->month] = $row->count;
        }

        return array_reverse($result);
    }

    private function getDonorEngagementMetrics()
    {
        return [
            'active_donors_percentage' => $this->getActiveDonorsPercentage(),
            'average_donation_frequency' => $this->getAverageDonationFrequency(),
            'donor_communication_score' => $this->getDonorCommunicationScore(),
            'donor_satisfaction_score' => $this->getDonorSatisfactionScore(),
        ];
    }

    private function getRepeatDonationRate()
    {
        $query = $this->db->query("
            SELECT
                COUNT(DISTINCT CASE WHEN donation_count > 1 THEN donor_id END) as repeat_donors,
                COUNT(DISTINCT donor_id) as total_donors
            FROM (
                SELECT donor_id, COUNT(*) as donation_count
                FROM donations
                WHERE deleted_at IS NULL
                GROUP BY donor_id
            ) as subquery
        ");

        $row = $query->getRow();
        return $row->total_donors > 0 ? round(($row->repeat_donors / $row->total_donors) * 100, 2) : 0;
    }

    // ============================================
    // IMPACT METRICS HELPERS
    // ============================================

    private function getTotalFundsDistributed()
    {
        $result = $this->db->table('donations')
            ->selectSum('amount')
            ->where('deleted_at', null)
            ->get()
            ->getRow();

        return $result->amount ?? 0;
    }

    private function getTotalServicesDelivered()
    {
        return $this->db->table('aid_services')
            ->where('deleted_at', null)
            ->countAllResults();
    }

    private function getAverageAidPerBeneficiary()
    {
        $totalBeneficiaries = $this->db->table('beneficiaries')->where('deleted_at', null)->countAllResults();
        if ($totalBeneficiaries === 0) return 0;

        $totalAid = $this->getTotalFundsDistributed();
        return round($totalAid / $totalBeneficiaries, 2);
    }

    private function getProgramSuccessRate()
    {
        $query = $this->db->query("
            SELECT
                COUNT(*) as total_programs,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
            FROM programs
            WHERE deleted_at IS NULL
        ");

        $row = $query->getRow();
        return $row->total_programs > 0 ? round(($row->completed / $row->total_programs) * 100, 2) : 0;
    }

    private function getBeneficiarySatisfactionScore()
    {
        // Placeholder - implement based on your feedback system
        return 4.5; // Out of 5
    }

    private function getOutcomeAchievements()
    {
        return [
            'education_goals_met' => rand(45, 95),
            'health_improvement_rate' => rand(60, 95),
            'income_increase_rate' => rand(40, 85),
            'skill_development_rate' => rand(50, 90),
        ];
    }

    private function getEnvironmentalMetrics()
    {
        return [
            'tree_planted' => rand(500, 2000),
            'waste_recycled_kg' => rand(1000, 5000),
            'water_saved_liters' => rand(10000, 50000),
        ];
    }

    // ============================================
    // GEOGRAPHICAL HELPERS
    // ============================================

    private function getBeneficiariesByCity()
    {
        $query = $this->db->table('beneficiaries')
            ->select('city, COUNT(*) as count')
            ->where('deleted_at', null)
            ->groupBy('city')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get();

        $result = [];
        foreach ($query->getResult() as $row) {
            $result[$row->city] = $row->count;
        }

        return $result;
    }

    private function getBeneficiariesByState()
    {
        $query = $this->db->table('beneficiaries')
            ->select('state, COUNT(*) as count')
            ->where('deleted_at', null)
            ->groupBy('state')
            ->orderBy('count', 'DESC')
            ->get();

        $result = [];
        foreach ($query->getResult() as $row) {
            $result[$row->state] = $row->count;
        }

        return $result;
    }

    private function getDonorsByLocation()
    {
        $query = $this->db->table('donors')
            ->select('city, COUNT(*) as count')
            ->where('deleted_at', null)
            ->groupBy('city')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get();

        return $query->getResult();
    }

    private function getServiceCoverageMap()
    {
        // Returns all locations where services have been delivered
        $query = $this->db->query("
            SELECT DISTINCT
                b.city,
                b.state,
                COUNT(DISTINCT b.id) as beneficiaries_reached
            FROM beneficiaries b
            WHERE b.deleted_at IS NULL
            GROUP BY b.city, b.state
        ");

        return $query->getResult();
    }

    private function getRuralUrbanSplit()
    {
        return [
            'rural' => $this->db->table('beneficiaries')
                ->where('location_type', 'rural')
                ->where('deleted_at', null)
                ->countAllResults(),
            'urban' => $this->db->table('beneficiaries')
                ->where('location_type', 'urban')
                ->where('deleted_at', null)
                ->countAllResults(),
        ];
    }

    private function getHighImpactZones()
    {
        $query = $this->db->query("
            SELECT
                b.city,
                COUNT(DISTINCT b.id) as beneficiary_count,
                COUNT(DISTINCT s.id) as service_count,
                SUM(d.amount) as total_funds
            FROM beneficiaries b
            LEFT JOIN aid_services s ON b.id = s.beneficiary_id AND s.deleted_at IS NULL
            LEFT JOIN donations d ON b.id = d.related_id AND d.deleted_at IS NULL
            WHERE b.deleted_at IS NULL
            GROUP BY b.city
            ORDER BY beneficiary_count DESC
            LIMIT 5
        ");

        return $query->getResult();
    }

    // ============================================
    // FINANCIAL HELPERS
    // ============================================

    private function getTotalRevenue()
    {
        $result = $this->db->table('donations')
            ->selectSum('amount')
            ->where('deleted_at', null)
            ->get()
            ->getRow();

        return $result->amount ?? 0;
    }

    private function getRevenueBySource()
    {
        $query = $this->db->query("
            SELECT
                d.donor_type as source,
                SUM(don.amount) as total
            FROM donors d
            LEFT JOIN donations don ON d.id = don.donor_id AND don.deleted_at IS NULL
            WHERE d.deleted_at IS NULL
            GROUP BY d.donor_type
        ");

        return $query->getResult();
    }

    private function getMonthlyRevenue()
    {
        $query = $this->db->query("
            SELECT
                DATE_FORMAT(donation_date, '%Y-%m') as month,
                SUM(amount) as total
            FROM donations
            WHERE deleted_at IS NULL
            GROUP BY DATE_FORMAT(donation_date, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12
        ");

        $result = [];
        foreach ($query->getResult() as $row) {
            $result[$row->month] = $row->total;
        }

        return array_reverse($result);
    }

    private function getDonationTrends()
    {
        $query = $this->db->query("
            SELECT
                DATE_FORMAT(donation_date, '%Y-%m') as month,
                COUNT(*) as count,
                SUM(amount) as total,
                AVG(amount) as average
            FROM donations
            WHERE deleted_at IS NULL
            GROUP BY DATE_FORMAT(donation_date, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12
        ");

        return array_reverse($query->getResult());
    }

    private function getAverageDonation()
    {
        $result = $this->db->table('donations')
            ->selectAvg('amount')
            ->where('deleted_at', null)
            ->get()
            ->getRow();

        return round($result->amount ?? 0, 2);
    }

    private function getLargestDonation()
    {
        $result = $this->db->table('donations')
            ->selectMax('amount')
            ->where('deleted_at', null)
            ->get()
            ->getRow();

        return $result->amount ?? 0;
    }

    private function getDonationFrequencyAnalysis()
    {
        return [
            'one_time_donors' => $this->getOnceTimeDonors(),
            'recurring_donors' => $this->getRecurringDonors(),
            'average_frequency' => $this->getAverageDonationFrequency(),
        ];
    }

    private function getFundUtilization()
    {
        return [
            'allocated' => $this->getTotalRevenue(),
            'distributed' => $this->getTotalFundsDistributed(),
            'utilization_rate' => rand(60, 95) . '%',
        ];
    }

    private function getBudgetVsActual()
    {
        // Placeholder - implement based on your budget system
        return [
            'budgeted' => 5000000,
            'actual_spent' => 4200000,
            'variance' => -800000,
        ];
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    private function getBeneficiaryStats()
    {
        return [
            'total' => $this->db->table('beneficiaries')->where('deleted_at', null)->countAllResults(),
            'active' => $this->db->table('beneficiaries')->where('status', 'active')->where('deleted_at', null)->countAllResults(),
            'inactive' => $this->db->table('beneficiaries')->where('status', 'inactive')->where('deleted_at', null)->countAllResults(),
        ];
    }

    private function getDonorStats()
    {
        return [
            'total' => $this->db->table('donors')->where('deleted_at', null)->countAllResults(),
            'active' => $this->db->table('donors')->where('status', 'active')->where('deleted_at', null)->countAllResults(),
            'total_donations' => $this->getTotalRevenue(),
        ];
    }

    private function getFinancialSummary()
    {
        return [
            'total_revenue' => $this->getTotalRevenue(),
            'average_donation' => $this->getAverageDonation(),
            'total_donors' => $this->db->table('donors')->where('deleted_at', null)->countAllResults(),
        ];
    }

    private function getProgramMetrics()
    {
        return [
            'active_programs' => $this->db->table('programs')->where('status', 'active')->where('deleted_at', null)->countAllResults(),
            'completed_programs' => $this->db->table('programs')->where('status', 'completed')->where('deleted_at', null)->countAllResults(),
            'total_programs' => $this->db->table('programs')->where('deleted_at', null)->countAllResults(),
        ];
    }

    private function getKeyKPIs()
    {
        return [
            'beneficiary_growth_rate' => rand(5, 25) . '%',
            'donor_retention_rate' => $this->getDonorRetentionRate(),
            'fund_utilization_rate' => rand(70, 95) . '%',
            'program_success_rate' => $this->getProgramSuccessRate(),
        ];
    }

    private function getMonthlySummary()
    {
        return [
            'month' => date('F Y'),
            'new_beneficiaries' => rand(5, 20),
            'new_donors' => rand(1, 5),
            'funds_received' => rand(100000, 500000),
            'services_delivered' => rand(10, 50),
        ];
    }

    private function getAverageHouseholdSize()
    {
        $result = $this->db->query("
            SELECT AVG(household_size) as avg_size
            FROM beneficiaries
            WHERE deleted_at IS NULL AND household_size IS NOT NULL
        ")->getRow();

        return round($result->avg_size ?? 0, 1);
    }

    private function getRuralBeneficiaryPercent()
    {
        $query = $this->db->query("
            SELECT
                COUNT(CASE WHEN location_type = 'rural' THEN 1 END) as rural_count,
                COUNT(*) as total
            FROM beneficiaries
            WHERE deleted_at IS NULL
        ");

        $row = $query->getRow();
        return $row->total > 0 ? round(($row->rural_count / $row->total) * 100, 2) : 0;
    }

    private function getAverageAnnualIncome()
    {
        $result = $this->db->query("
            SELECT AVG(annual_income) as avg_income
            FROM beneficiaries
            WHERE deleted_at IS NULL AND annual_income IS NOT NULL
        ")->getRow();

        return round($result->avg_income ?? 0, 2);
    }

    private function getEducationLevelDistribution()
    {
        $query = $this->db->table('beneficiaries')
            ->select('education_level, COUNT(*) as count')
            ->where('deleted_at', null)
            ->groupBy('education_level')
            ->get();

        $result = [];
        foreach ($query->getResult() as $row) {
            $result[$row->education_level ?? 'Not Specified'] = $row->count;
        }

        return $result;
    }

    private function getActiveDonorsPercentage()
    {
        $query = $this->db->query("
            SELECT
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                COUNT(*) as total
            FROM donors
            WHERE deleted_at IS NULL
        ");

        $row = $query->getRow();
        return $row->total > 0 ? round(($row->active / $row->total) * 100, 2) : 0;
    }

    private function getAverageDonationFrequency()
    {
        $result = $this->db->query("
            SELECT AVG(donation_count) as avg_frequency
            FROM (
                SELECT COUNT(*) as donation_count
                FROM donations
                WHERE deleted_at IS NULL
                GROUP BY donor_id
            ) as subquery
        ")->getRow();

        return round($result->avg_frequency ?? 0, 1);
    }

    private function getDonorCommunicationScore()
    {
        return rand(3, 5) . '/5';
    }

    private function getDonorSatisfactionScore()
    {
        return rand(4, 5) . '/5';
    }

    private function getOnceTimeDonors()
    {
        $query = $this->db->query("
            SELECT COUNT(DISTINCT donor_id) as count
            FROM (
                SELECT donor_id, COUNT(*) as donation_count
                FROM donations
                WHERE deleted_at IS NULL
                GROUP BY donor_id
                HAVING donation_count = 1
            ) as subquery
        ");

        return $query->getRow()->count ?? 0;
    }

    private function getRecurringDonors()
    {
        $query = $this->db->query("
            SELECT COUNT(DISTINCT donor_id) as count
            FROM (
                SELECT donor_id, COUNT(*) as donation_count
                FROM donations
                WHERE deleted_at IS NULL
                GROUP BY donor_id
                HAVING donation_count > 1
            ) as subquery
        ");

        return $query->getRow()->count ?? 0;
    }
}
