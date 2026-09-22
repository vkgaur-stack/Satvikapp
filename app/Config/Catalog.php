<?php

namespace Config;

use App\Models\AidDeliveryModel;
use App\Models\BeneficiaryModel;
use App\Models\CampaignModel;
use App\Models\CaseNoteModel;
use App\Models\DocumentModel;
use App\Models\DonationModel;
use App\Models\DonorModel;
use App\Models\EnrollmentModel;
use App\Models\HouseholdModel;
use App\Models\ProgramModel;
use App\Models\SurveyModel;
use App\Models\UserModel;
use CodeIgniter\Config\BaseConfig;

/**
 * The module catalog. Every screen (list, form, detail) and every REST endpoint is generated
 * from this file - to add a field or a whole module you edit here plus one Model.
 *
 * Field keys:  name, label, type (text|email|tel|number|money|date|textarea|select|fk|password|checkbox|file),
 *   rules (CI4 validation), list (show in tables), form (true|false|'create'|'edit'), show (detail page),
 *   options (for select), fk (['module'=>slug] or ['table'=>..,'title'=>..]),
 *   mask ('pii' => needs pii.decrypt, 'identity' => needs identity.view; otherwise value is hidden),
 *   virtual (computed by the module's "select"), store (file column map).
 */
class Catalog extends BaseConfig
{
    /** @var array<string,array> */
    public array $modules = [];

    public function __construct()
    {
        parent::__construct();

        $f = static fn (string $name, string $label, string $type = 'text', array $o = []): array => ['name' => $name, 'label' => $label, 'type' => $type] + $o;
        $opt = static function (array $keys): array {
            $out = [];
            foreach ($keys as $k => $v) {
                if (is_int($k)) {
                    $out[$v] = ucwords(str_replace('_', ' ', $v));
                } else {
                    $out[$k] = $v;
                }
            }

            return $out;
        };

        $this->modules = [
            // ------------------------------------------------------------------ FUNDRAISING
            'donors' => [
                'label' => 'Donors', 'singular' => 'Donor', 'icon' => 'bi-heart', 'group' => 'Fundraising',
                'model' => DonorModel::class, 'title' => 'name', 'search' => ['name', 'email', 'phone', 'city'],
                'filters' => ['tier', 'status'], 'order' => ['id', 'DESC'], 'owner_field' => 'created_by', 'api' => true,
                'hidden' => [],
                'related' => [['module' => 'donations', 'fk' => 'donor_id', 'label' => 'Giving history']],
                'fields' => [
                    $f('name', 'Name', 'text', ['rules' => 'required|min_length[2]|max_length[150]', 'list' => true, 'mask' => 'identity']),
                    $f('email', 'Email', 'email', ['rules' => 'permit_empty|valid_email|max_length[150]', 'list' => true, 'mask' => 'pii']),
                    $f('phone', 'Phone', 'tel', ['rules' => 'permit_empty|max_length[20]', 'mask' => 'pii']),
                    $f('address', 'Address', 'text', ['rules' => 'permit_empty|max_length[255]', 'mask' => 'pii', 'show' => true]),
                    $f('city', 'City', 'text', ['rules' => 'permit_empty|max_length[80]', 'list' => true]),
                    $f('state', 'State', 'text', ['rules' => 'permit_empty|max_length[80]']),
                    $f('pan', 'PAN (for 80G receipts)', 'text', ['rules' => 'permit_empty|regex_match[/^[A-Z]{5}[0-9]{4}[A-Z]$/]', 'mask' => 'pii', 'help' => 'Format: ABCDE1234F']),
                    $f('communication_preference', 'Contact preference', 'select', ['options' => $opt(['email', 'whatsapp', 'both', 'none']), 'rules' => 'required']),
                    $f('status', 'Status', 'select', ['options' => $opt(['active', 'lapsed', 'inactive']), 'rules' => 'required', 'form' => 'edit', 'list' => true, 'help' => 'Active/Lapsed is set automatically from the last gift (6 months). Use Inactive to stop outreach.']),
                    $f('tier', 'Tier', 'select', ['options' => $opt(['bronze', 'silver', 'gold', 'major']), 'form' => false, 'list' => true]),
                    $f('lifetime_value', 'Lifetime value', 'money', ['form' => false, 'list' => true]),
                    $f('last_gift_date', 'Last gift', 'date', ['form' => false, 'list' => true]),
                    $f('notes', 'Notes', 'textarea', ['rules' => 'permit_empty|max_length[2000]']),
                ],
            ],
            'donations' => [
                'label' => 'Donations', 'singular' => 'Donation', 'icon' => 'bi-cash-coin', 'group' => 'Fundraising',
                'model' => DonationModel::class, 'title' => 'id', 'search' => ['receipt_no', 'reference_no'],
                'filters' => ['payment_method', 'frequency', 'receipt_status'], 'order' => ['donated_on', 'DESC'], 'owner_field' => 'created_by', 'api' => true,
                'hidden' => [], 'redirect_after_create' => 'receipts/{id}',
                'row_actions' => [['label' => 'Receipt', 'url' => 'receipts/{id}', 'icon' => 'bi-receipt', 'perm' => 'donations.view']],
                'fields' => [
                    $f('donor_id', 'Donor', 'fk', ['fk' => ['module' => 'donors'], 'rules' => 'required', 'list' => true]),
                    $f('campaign_id', 'Campaign', 'fk', ['fk' => ['module' => 'campaigns'], 'rules' => 'permit_empty', 'list' => true]),
                    $f('amount', 'Amount (INR)', 'money', ['rules' => 'required|numeric|greater_than[0]|less_than[100000000]', 'list' => true]),
                    $f('donated_on', 'Date received', 'date', ['rules' => 'required|valid_date[Y-m-d]', 'list' => true]),
                    $f('payment_method', 'Payment method', 'select', ['options' => $opt(['online', 'cheque', 'cash', 'bank_transfer', 'in_kind']), 'rules' => 'required', 'list' => true]),
                    $f('frequency', 'Frequency', 'select', ['options' => $opt(['one_off' => 'One-off', 'monthly' => 'Monthly', 'annual' => 'Annual']), 'rules' => 'required', 'list' => true]),
                    $f('reference_no', 'Cheque no. / UTR / in-kind reference', 'text', ['rules' => 'permit_empty|max_length[80]']),
                    $f('payment_status', 'Payment status', 'select', ['options' => $opt(['pending', 'captured', 'failed']), 'form' => false, 'list' => true]),
                    $f('receipt_status', 'Tax receipt', 'select', ['options' => $opt(['pending', 'issued']), 'form' => false, 'list' => true]),
                    $f('receipt_no', 'Receipt no.', 'text', ['form' => false]),
                    $f('notes', 'Notes', 'textarea', ['rules' => 'permit_empty|max_length[500]']),
                ],
            ],
            'campaigns' => [
                'label' => 'Campaigns', 'singular' => 'Campaign', 'icon' => 'bi-bullseye', 'group' => 'Fundraising',
                'model' => CampaignModel::class, 'title' => 'name', 'search' => ['name'], 'filters' => ['category'],
                'order' => ['id', 'DESC'], 'api' => true, 'hidden' => [],
                'select' => ['campaigns.*', "(SELECT COALESCE(SUM(d.amount),0) FROM donations d WHERE d.campaign_id = campaigns.id AND d.payment_status = 'captured' AND d.deleted_at IS NULL) AS raised"],
                'related' => [['module' => 'donations', 'fk' => 'campaign_id', 'label' => 'Donations to this campaign']],
                'fields' => [
                    $f('name', 'Campaign name', 'text', ['rules' => 'required|min_length[3]|max_length[150]', 'list' => true]),
                    $f('category', 'Category', 'select', ['options' => $opt(['general', 'healthcare', 'education', 'vocational', 'food', 'disaster_relief']), 'rules' => 'required', 'list' => true]),
                    $f('target_goal', 'Target goal (INR)', 'money', ['rules' => 'required|numeric|greater_than_equal_to[0]', 'list' => true]),
                    $f('raised', 'Raised so far', 'money', ['virtual' => true, 'form' => false, 'list' => true]),
                    $f('start_date', 'Start date', 'date', ['rules' => 'permit_empty|valid_date[Y-m-d]', 'list' => true]),
                    $f('end_date', 'End date', 'date', ['rules' => 'permit_empty|valid_date[Y-m-d]', 'list' => true]),
                    $f('description', 'Description', 'textarea', ['rules' => 'permit_empty|max_length[2000]']),
                ],
            ],

            // ------------------------------------------------------------------ BENEFICIARIES
            'beneficiaries' => [
                'label' => 'Beneficiaries', 'singular' => 'Beneficiary', 'icon' => 'bi-person-heart', 'group' => 'Programs',
                'model' => BeneficiaryModel::class, 'title' => 'full_name', 'search' => ['full_name', 'village', 'district'],
                'filters' => ['status', 'category', 'dedup_flag'], 'order' => ['id', 'DESC'], 'owner_field' => 'created_by', 'pii' => true, 'api' => true,
                'hidden' => ['phone_hash', 'id_number_hash'],
                'related' => [
                    ['module' => 'households', 'fk' => 'beneficiary_id', 'label' => 'Household'],
                    ['module' => 'enrollments', 'fk' => 'beneficiary_id', 'label' => 'Program enrolments'],
                    ['module' => 'aid_deliveries', 'fk' => 'beneficiary_id', 'label' => 'Aid received'],
                    ['module' => 'case_notes', 'fk' => 'beneficiary_id', 'label' => 'Case notes'],
                    ['module' => 'documents', 'fk' => 'beneficiary_id', 'label' => 'Documents'],
                    ['module' => 'surveys', 'fk' => 'beneficiary_id', 'label' => 'Surveys'],
                ],
                'fields' => [
                    $f('full_name', 'Full name', 'text', ['rules' => 'required|min_length[2]|max_length[150]', 'list' => true, 'mask' => 'identity']),
                    $f('gender', 'Gender', 'select', ['options' => $opt(['female', 'male', 'other']), 'rules' => 'required', 'list' => true]),
                    $f('dob', 'Date of birth', 'date', ['rules' => 'permit_empty|valid_date[Y-m-d]']),
                    $f('category', 'Beneficiary group', 'select', ['options' => $opt(['rural_woman' => 'Rural woman', 'marginalized_youth' => 'Marginalized youth', 'disaster_affected' => 'Disaster-affected family', 'underprivileged_family' => 'Underprivileged family']), 'rules' => 'required', 'list' => true]),
                    $f('phone', 'Phone', 'tel', ['rules' => 'permit_empty|max_length[20]', 'mask' => 'pii']),
                    $f('email', 'Email', 'email', ['rules' => 'permit_empty|valid_email|max_length[150]', 'mask' => 'pii']),
                    $f('id_type', 'ID type', 'select', ['options' => $opt(['aadhaar', 'voter_id' => 'Voter ID', 'pan' => 'PAN', 'driving_license' => 'Driving licence', 'ration_card', 'none' => 'No ID available']), 'rules' => 'required']),
                    $f('id_number', 'ID number', 'text', ['rules' => 'permit_empty|max_length[30]', 'mask' => 'pii', 'help' => 'Stored encrypted. Duplicates are detected automatically.']),
                    $f('no_id_reason', 'If no ID: reason', 'text', ['rules' => 'permit_empty|max_length[255]', 'help' => 'e.g. application pending, documents lost. Add a substitute document under Documents.']),
                    $f('address', 'Address', 'textarea', ['rules' => 'permit_empty|max_length[500]', 'mask' => 'pii']),
                    $f('village', 'Village / locality', 'text', ['rules' => 'permit_empty|max_length[100]', 'list' => true]),
                    $f('district', 'District', 'text', ['rules' => 'permit_empty|max_length[100]', 'list' => true]),
                    $f('state', 'State', 'text', ['rules' => 'permit_empty|max_length[80]']),
                    $f('pincode', 'PIN code', 'text', ['rules' => 'permit_empty|exact_length[6]|numeric']),
                    $f('consent_given', 'Consent form signed', 'checkbox'),
                    $f('consent_date', 'Consent date', 'date', ['rules' => 'permit_empty|valid_date[Y-m-d]']),
                    $f('status', 'Status', 'select', ['options' => $opt(['registered', 'verified', 'active', 'graduated', 'inactive']), 'rules' => 'required', 'form' => 'edit', 'list' => true]),
                    $f('dedup_flag', 'Possible duplicate', 'select', ['options' => ['0' => 'No', '1' => 'Flagged'], 'form' => false, 'list' => true]),
                    $f('dedup_of', 'Matches record #', 'number', ['form' => false]),
                ],
            ],
            'households' => [
                'label' => 'Households', 'singular' => 'Household', 'icon' => 'bi-house-heart', 'group' => 'Programs',
                'model' => HouseholdModel::class, 'title' => 'id', 'search' => [], 'filters' => [], 'order' => ['id', 'DESC'], 'owner_field' => 'created_by', 'api' => true, 'hidden' => [],
                'fields' => [
                    $f('beneficiary_id', 'Head of household', 'fk', ['fk' => ['module' => 'beneficiaries'], 'rules' => 'required', 'list' => true]),
                    $f('members_count', 'Members', 'number', ['rules' => 'required|is_natural_no_zero|less_than[60]', 'list' => true]),
                    $f('monthly_income', 'Monthly income (INR)', 'money', ['rules' => 'permit_empty|numeric|greater_than_equal_to[0]', 'list' => true]),
                    $f('latitude', 'Latitude', 'number', ['rules' => 'permit_empty|decimal', 'step' => 'any']),
                    $f('longitude', 'Longitude', 'number', ['rules' => 'permit_empty|decimal', 'step' => 'any']),
                    $f('notes', 'Notes', 'textarea', ['rules' => 'permit_empty|max_length[500]']),
                ],
            ],
            'programs' => [
                'label' => 'Programs', 'singular' => 'Program', 'icon' => 'bi-diagram-3', 'group' => 'Programs',
                'model' => ProgramModel::class, 'title' => 'name', 'search' => ['name'], 'filters' => ['category', 'status'], 'order' => ['id', 'DESC'], 'api' => true, 'hidden' => [],
                'select' => ['programs.*', "(SELECT COUNT(*) FROM enrollments e WHERE e.program_id = programs.id AND e.status IN ('enrolled','completed') AND e.deleted_at IS NULL) AS enrolled_count"],
                'related' => [['module' => 'enrollments', 'fk' => 'program_id', 'label' => 'Enrolments']],
                'fields' => [
                    $f('name', 'Program name', 'text', ['rules' => 'required|min_length[3]|max_length[150]', 'list' => true]),
                    $f('category', 'Area', 'select', ['options' => $opt(['healthcare', 'education', 'vocational', 'food']), 'rules' => 'required', 'list' => true]),
                    $f('status', 'Status', 'select', ['options' => $opt(['active', 'paused', 'completed']), 'rules' => 'required', 'list' => true]),
                    $f('target_beneficiaries', 'Seats / target', 'number', ['rules' => 'required|is_natural', 'list' => true]),
                    $f('enrolled_count', 'Enrolled', 'number', ['virtual' => true, 'form' => false, 'list' => true]),
                    $f('budget', 'Budget (INR)', 'money', ['rules' => 'required|numeric|greater_than_equal_to[0]']),
                    $f('start_date', 'Start date', 'date', ['rules' => 'permit_empty|valid_date[Y-m-d]']),
                    $f('end_date', 'End date', 'date', ['rules' => 'permit_empty|valid_date[Y-m-d]']),
                    $f('gender_target', 'Gender target', 'select', ['options' => $opt(['any', 'female', 'male']), 'rules' => 'required', 'help' => 'Eligibility rule']),
                    $f('min_age', 'Minimum age', 'number', ['rules' => 'permit_empty|is_natural|less_than[120]', 'help' => 'Eligibility rule']),
                    $f('max_age', 'Maximum age', 'number', ['rules' => 'permit_empty|is_natural|less_than[120]']),
                    $f('max_monthly_income', 'Household income ceiling (INR / month)', 'money', ['rules' => 'permit_empty|numeric|greater_than_equal_to[0]']),
                    $f('description', 'Description', 'textarea', ['rules' => 'permit_empty|max_length[2000]']),
                ],
            ],
            'enrollments' => [
                'label' => 'Enrolments', 'singular' => 'Enrolment', 'icon' => 'bi-clipboard-check', 'group' => 'Programs',
                'model' => EnrollmentModel::class, 'title' => 'id', 'search' => [], 'filters' => ['status', 'program_id'], 'order' => ['id', 'DESC'], 'owner_field' => 'created_by', 'api' => true, 'hidden' => [],
                'related' => [['module' => 'aid_deliveries', 'fk' => 'enrollment_id', 'label' => 'Aid delivered']],
                'fields' => [
                    $f('beneficiary_id', 'Beneficiary', 'fk', ['fk' => ['module' => 'beneficiaries'], 'rules' => 'required', 'list' => true]),
                    $f('program_id', 'Program', 'fk', ['fk' => ['module' => 'programs'], 'rules' => 'required', 'list' => true]),
                    $f('status', 'Status', 'select', ['options' => $opt(['pending', 'eligible', 'ineligible', 'enrolled', 'completed', 'dropped']), 'rules' => 'required', 'form' => 'edit', 'list' => true, 'help' => 'Set automatically by eligibility screening when created. A manager moves it to Enrolled / Completed / Dropped.']),
                    $f('eligibility_score', 'Eligibility score', 'number', ['form' => false, 'list' => true]),
                    $f('eligibility_notes', 'Screening result', 'text', ['form' => false]),
                    $f('enrolled_on', 'Enrolled on', 'date', ['rules' => 'permit_empty|valid_date[Y-m-d]', 'form' => 'edit']),
                    $f('completed_on', 'Completed on', 'date', ['rules' => 'permit_empty|valid_date[Y-m-d]', 'form' => 'edit']),
                ],
            ],
            'aid_deliveries' => [
                'label' => 'Aid deliveries', 'singular' => 'Aid delivery', 'icon' => 'bi-box2-heart', 'group' => 'Programs',
                'model' => AidDeliveryModel::class, 'title' => 'id', 'search' => ['description'], 'filters' => ['delivery_type', 'status', 'program_id'], 'order' => ['delivered_on', 'DESC'], 'owner_field' => 'created_by', 'api' => true,
                'hidden' => ['proof_file'],
                'fields' => [
                    $f('beneficiary_id', 'Beneficiary', 'fk', ['fk' => ['module' => 'beneficiaries'], 'rules' => 'required', 'list' => true]),
                    $f('program_id', 'Program', 'fk', ['fk' => ['module' => 'programs'], 'rules' => 'required', 'list' => true]),
                    $f('delivery_type', 'Type', 'select', ['options' => $opt(['cash', 'in_kind' => 'In-kind', 'service']), 'rules' => 'required', 'list' => true]),
                    $f('amount', 'Amount / value (INR)', 'money', ['rules' => 'permit_empty|numeric|greater_than_equal_to[0]', 'list' => true]),
                    $f('description', 'What was delivered', 'text', ['rules' => 'permit_empty|max_length[500]']),
                    $f('delivered_on', 'Date', 'date', ['rules' => 'required|valid_date[Y-m-d]', 'list' => true]),
                    $f('status', 'Status', 'select', ['options' => $opt(['pending', 'delivered', 'verified']), 'rules' => 'required', 'list' => true]),
                    $f('proof_file', 'Proof of delivery (photo / signature, max 5 MB)', 'file', ['store' => ['path' => 'proof_file', 'name' => 'proof_name']]),
                ],
            ],
            'case_notes' => [
                'label' => 'Case notes', 'singular' => 'Case note', 'icon' => 'bi-journal-text', 'group' => 'Programs',
                'model' => CaseNoteModel::class, 'title' => 'title', 'search' => ['title'], 'filters' => ['note_type', 'status'], 'order' => ['id', 'DESC'], 'owner_field' => 'created_by', 'pii' => true, 'api' => true,
                'hidden' => [],
                'fields' => [
                    $f('beneficiary_id', 'Beneficiary', 'fk', ['fk' => ['module' => 'beneficiaries'], 'rules' => 'required', 'list' => true]),
                    $f('note_type', 'Type', 'select', ['options' => $opt(['progress', 'referral', 'grievance', 'counseling']), 'rules' => 'required', 'list' => true]),
                    $f('title', 'Title', 'text', ['rules' => 'required|max_length[200]', 'list' => true]),
                    $f('content', 'Note (stored encrypted)', 'textarea', ['rules' => 'required|max_length[10000]', 'mask' => 'pii']),
                    $f('action_items', 'Action items / resolution steps', 'textarea', ['rules' => 'permit_empty|max_length[1000]']),
                    $f('status', 'Status', 'select', ['options' => $opt(['open', 'resolved']), 'rules' => 'required', 'form' => 'edit', 'list' => true]),
                    $f('resolved_at', 'Resolved at', 'date', ['form' => false]),
                ],
            ],
            'documents' => [
                'label' => 'Documents', 'singular' => 'Document', 'icon' => 'bi-folder2-open', 'group' => 'Programs',
                'model' => DocumentModel::class, 'title' => 'title', 'search' => ['title'], 'filters' => ['document_type'], 'order' => ['id', 'DESC'], 'owner_field' => 'created_by', 'pii' => true, 'api' => true,
                'hidden' => ['file_path'],
                'row_actions' => [['label' => 'Open', 'url' => 'files/documents/{id}', 'icon' => 'bi-download', 'perm' => 'pii.decrypt']],
                'fields' => [
                    $f('beneficiary_id', 'Beneficiary', 'fk', ['fk' => ['module' => 'beneficiaries'], 'rules' => 'required', 'list' => true]),
                    $f('document_type', 'Type', 'select', ['options' => $opt(['id' => 'ID card', 'medical' => 'Medical certificate', 'education' => 'Mark sheet / education', 'consent' => 'Consent form', 'other']), 'rules' => 'required', 'list' => true]),
                    $f('title', 'Title', 'text', ['rules' => 'required|max_length[200]', 'list' => true]),
                    $f('file_path', 'File (JPG, PNG or PDF, max 5 MB)', 'file', ['required' => true, 'store' => ['path' => 'file_path', 'name' => 'original_name', 'mime' => 'mime_type', 'size' => 'file_size']]),
                    $f('verified_at', 'Verified on', 'date', ['form' => false, 'list' => true]),
                ],
            ],
            'surveys' => [
                'label' => 'Surveys', 'singular' => 'Survey', 'icon' => 'bi-graph-up-arrow', 'group' => 'Programs',
                'model' => SurveyModel::class, 'title' => 'id', 'search' => [], 'filters' => ['survey_type'], 'order' => ['surveyed_on', 'DESC'], 'owner_field' => 'created_by', 'api' => true, 'hidden' => [],
                'fields' => [
                    $f('beneficiary_id', 'Beneficiary', 'fk', ['fk' => ['module' => 'beneficiaries'], 'rules' => 'required', 'list' => true]),
                    $f('program_id', 'Program', 'fk', ['fk' => ['module' => 'programs'], 'rules' => 'permit_empty', 'list' => true]),
                    $f('survey_type', 'Survey', 'select', ['options' => $opt(['baseline', 'endline', 'post_distribution' => 'Post-distribution']), 'rules' => 'required', 'list' => true]),
                    $f('monthly_income', 'Household income (INR / month)', 'money', ['rules' => 'permit_empty|numeric|greater_than_equal_to[0]', 'list' => true]),
                    $f('outcome_score', 'Outcome score (0-100)', 'number', ['rules' => 'permit_empty|is_natural|less_than_equal_to[100]', 'list' => true]),
                    $f('surveyed_on', 'Surveyed on', 'date', ['rules' => 'required|valid_date[Y-m-d]', 'list' => true]),
                    $f('notes', 'Notes', 'textarea', ['rules' => 'permit_empty|max_length[500]']),
                ],
            ],

            // ------------------------------------------------------------------ ADMIN
            'users' => [
                'label' => 'Users', 'singular' => 'User', 'icon' => 'bi-shield-lock', 'group' => 'Administration',
                'model' => UserModel::class, 'title' => 'name', 'search' => ['name', 'email'], 'filters' => ['status'], 'order' => ['id', 'ASC'], 'api' => false,
                'hidden' => ['password_hash', 'password'],
                'fields' => [
                    $f('name', 'Full name', 'text', ['rules' => 'required|min_length[2]|max_length[120]', 'list' => true]),
                    $f('email', 'Email (login)', 'email', ['rules' => 'required|valid_email|max_length[150]|is_unique[users.email,id,{id}]', 'list' => true]),
                    $f('phone', 'Phone', 'tel', ['rules' => 'permit_empty|max_length[20]']),
                    $f('role_id', 'Role', 'fk', ['fk' => ['table' => 'roles', 'title' => 'label'], 'rules' => 'required', 'list' => true]),
                    $f('password', 'Password', 'password', ['show' => false, 'rules' => 'required|min_length[10]|max_length[72]', 'rules_edit' => 'permit_empty|min_length[10]|max_length[72]', 'help' => 'At least 10 characters. On edit, leave blank to keep the current password.']),
                    $f('status', 'Status', 'select', ['options' => $opt(['active', 'disabled']), 'rules' => 'required', 'list' => true]),
                ],
            ],
        ];
    }
}
