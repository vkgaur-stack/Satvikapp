<?php

namespace App\Models;

class AidDeliveryModel extends BaseModel
{
    protected $table         = 'aid_deliveries';
    protected $allowedFields = ['beneficiary_id', 'program_id', 'enrollment_id', 'delivery_type', 'amount', 'description', 'delivered_on', 'status', 'proof_file', 'proof_name', 'verified_by', 'created_by'];
    protected $beforeInsert  = ['linkEnrollment'];
    protected $beforeUpdate  = ['stampVerifier'];

    /** Attach the delivery to the beneficiary's active enrolment in that program, if there is one. */
    protected function linkEnrollment(array $data): array
    {
        $d = $data['data'];
        if (empty($d['enrollment_id']) && ! empty($d['beneficiary_id']) && ! empty($d['program_id'])) {
            $e = db_connect()->table('enrollments')->select('id')
                ->where('beneficiary_id', $d['beneficiary_id'])->where('program_id', $d['program_id'])
                ->whereIn('status', ['eligible', 'enrolled', 'completed'])->where('deleted_at', null)
                ->orderBy('id', 'DESC')->get(1)->getRowArray();
            if ($e) {
                $data['data']['enrollment_id'] = (int) $e['id'];
            }
        }

        return $data;
    }

    protected function stampVerifier(array $data): array
    {
        if (($data['data']['status'] ?? null) === 'verified' && \App\Libraries\CurrentUser::id()) {
            $data['data']['verified_by'] = \App\Libraries\CurrentUser::id();
        }

        return $data;
    }
}
