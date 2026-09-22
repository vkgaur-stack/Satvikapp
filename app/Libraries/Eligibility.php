<?php

namespace App\Libraries;

/**
 * Rule-based programme screening. Each program carries its own rules
 * (age band, gender target, household income ceiling, capacity).
 */
class Eligibility
{
    /** @return array{status:string,score:int,notes:string} */
    public static function evaluate(int $beneficiaryId, int $programId): array
    {
        $db   = db_connect();
        $ben  = $db->table('beneficiaries')->where('id', $beneficiaryId)->where('deleted_at', null)->get()->getRowArray();
        $prog = $db->table('programs')->where('id', $programId)->where('deleted_at', null)->get()->getRowArray();
        if (! $ben || ! $prog) {
            return ['status' => 'ineligible', 'score' => 0, 'notes' => 'Beneficiary or program not found.'];
        }

        $hh = $db->table('households')->where('beneficiary_id', $beneficiaryId)->where('deleted_at', null)->orderBy('id', 'DESC')->get(1)->getRowArray();

        $checks  = [];   // label => true (pass) | false (fail) | null (cannot tell yet)
        $checks['Program is open']    = $prog['status'] === 'active';
        $existing = $db->table('enrollments')->where('beneficiary_id', $beneficiaryId)->where('program_id', $programId)
            ->whereNotIn('status', ['ineligible', 'dropped'])->where('deleted_at', null)->countAllResults();
        $checks['Not already enrolled'] = $existing === 0;

        if ($prog['target_beneficiaries'] > 0) {
            $taken = $db->table('enrollments')->where('program_id', $programId)->whereIn('status', ['enrolled', 'completed'])->where('deleted_at', null)->countAllResults();
            $checks['Seat available'] = $taken < (int) $prog['target_beneficiaries'];
        }
        if ($prog['gender_target'] !== 'any') {
            $checks['Gender target (' . $prog['gender_target'] . ')'] = $ben['gender'] === $prog['gender_target'];
        }
        if ($prog['min_age'] !== null || $prog['max_age'] !== null) {
            if (empty($ben['dob'])) {
                $checks['Age band'] = null;
            } else {
                $age = (int) date_diff(date_create($ben['dob']), date_create('today'))->y;
                $checks['Age band ' . ($prog['min_age'] ?? 0) . '-' . ($prog['max_age'] ?? '∞')] =
                    $age >= (int) ($prog['min_age'] ?? 0) && $age <= (int) ($prog['max_age'] ?? 200);
            }
        }
        if ($prog['max_monthly_income'] !== null) {
            $checks['Household income ceiling'] = ($hh && $hh['monthly_income'] !== null)
                ? (float) $hh['monthly_income'] <= (float) $prog['max_monthly_income']
                : null;
        }

        $failed  = array_keys(array_filter($checks, static fn ($v) => $v === false));
        $unknown = array_keys(array_filter($checks, static fn ($v) => $v === null));
        $passed  = count(array_filter($checks, static fn ($v) => $v === true));
        $score   = (int) round(100 * $passed / max(1, count($checks)));

        if ($failed) {
            return ['status' => 'ineligible', 'score' => $score, 'notes' => 'Failed: ' . implode('; ', $failed)];
        }
        if ($unknown) {
            return ['status' => 'pending', 'score' => $score, 'notes' => 'Missing data for: ' . implode('; ', $unknown) . '. Add it and re-screen.'];
        }

        return ['status' => 'eligible', 'score' => 100, 'notes' => 'All checks passed'];
    }
}
