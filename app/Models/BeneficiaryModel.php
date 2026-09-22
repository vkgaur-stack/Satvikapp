<?php

namespace App\Models;

use App\Models\Traits\EncryptsFields;

/**
 * Phone, e-mail, national ID and address are AES-256 encrypted at rest.
 * Keyed hashes of phone and ID are kept so duplicates can be found without decrypting anything.
 */
class BeneficiaryModel extends BaseModel
{
    use EncryptsFields;

    protected $table         = 'beneficiaries';
    protected $allowedFields = [
        'full_name', 'gender', 'dob', 'phone', 'phone_hash', 'email', 'id_type', 'id_number', 'id_number_hash', 'no_id_reason',
        'address', 'village', 'district', 'state', 'pincode', 'category', 'status', 'consent_given', 'consent_date',
        'dedup_flag', 'dedup_of', 'created_by',
    ];

    protected array $encryptedFields = ['phone', 'email', 'id_number', 'address'];
    protected array $hashedFields    = ['phone' => 'phone_hash', 'id_number' => 'id_number_hash'];

    protected $beforeInsert = ['encryptFields', 'flagDuplicates'];
    protected $beforeUpdate = ['encryptFields'];
    protected $afterFind    = ['decryptFields'];

    /**
     * Duplicate screening. A match never blocks registration (a field worker may be right and the
     * old record wrong) - it flags the record for the Program Manager's review queue.
     */
    protected function flagDuplicates(array $data): array
    {
        $d  = $data['data'];
        $tb = db_connect()->table('beneficiaries');
        $match = null;

        if (! empty($d['id_number_hash'])) {
            $r = (clone $tb)->select('id')->where('id_number_hash', $d['id_number_hash'])->where('deleted_at', null)->get(1)->getRowArray();
            $match = $r['id'] ?? null;
        }
        if (! $match && ! empty($d['phone_hash']) && ! empty($d['dob']) && ! empty($d['full_name'])) {
            $r = (clone $tb)->select('id')->where('phone_hash', $d['phone_hash'])->where('dob', $d['dob'])->where('full_name', $d['full_name'])->where('deleted_at', null)->get(1)->getRowArray();
            $match = $r['id'] ?? null;
        }
        if (! $match && ! empty($d['full_name']) && ! empty($d['dob']) && ! empty($d['village'])) {
            $r = (clone $tb)->select('id')->where('full_name', $d['full_name'])->where('dob', $d['dob'])->where('village', $d['village'])->where('deleted_at', null)->get(1)->getRowArray();
            $match = $r['id'] ?? null;
        }
        if ($match) {
            $data['data']['dedup_flag'] = 1;
            $data['data']['dedup_of']   = (int) $match;
        }

        return $data;
    }
}
