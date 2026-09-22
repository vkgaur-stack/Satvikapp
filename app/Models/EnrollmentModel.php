<?php

namespace App\Models;

use App\Libraries\Eligibility;

class EnrollmentModel extends BaseModel
{
    protected $table         = 'enrollments';
    protected $allowedFields = ['beneficiary_id', 'program_id', 'status', 'eligibility_score', 'eligibility_notes', 'enrolled_on', 'completed_on', 'verified_by', 'created_by'];
    protected $beforeInsert  = ['screen'];
    protected $beforeUpdate  = ['stamp'];

    /** Rule-based eligibility screening runs automatically when an enrolment is created. */
    protected function screen(array $data): array
    {
        $r = Eligibility::evaluate((int) $data['data']['beneficiary_id'], (int) $data['data']['program_id']);
        $data['data']['status']            = $r['status'];
        $data['data']['eligibility_score'] = $r['score'];
        $data['data']['eligibility_notes'] = mb_substr($r['notes'], 0, 500);

        return $data;
    }

    /** Fill in dates and the verifying user when a manager moves an enrolment along. */
    protected function stamp(array $data): array
    {
        $status = $data['data']['status'] ?? null;
        if ($status === 'enrolled' && empty($data['data']['enrolled_on'])) {
            $data['data']['enrolled_on'] = date('Y-m-d');
        }
        if ($status === 'completed' && empty($data['data']['completed_on'])) {
            $data['data']['completed_on'] = date('Y-m-d');
        }
        if (in_array($status, ['enrolled', 'completed', 'eligible'], true) && \App\Libraries\CurrentUser::id()) {
            $data['data']['verified_by'] = \App\Libraries\CurrentUser::id();
        }

        return $data;
    }
}
