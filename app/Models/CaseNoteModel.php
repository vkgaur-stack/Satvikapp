<?php

namespace App\Models;

use App\Models\Traits\EncryptsFields;

/** Counselling, referral and grievance notes: the note body is encrypted at rest. */
class CaseNoteModel extends BaseModel
{
    use EncryptsFields;

    protected $table         = 'case_notes';
    protected $allowedFields = ['beneficiary_id', 'note_type', 'title', 'content', 'action_items', 'status', 'resolved_at', 'created_by'];

    protected array $encryptedFields = ['content'];
    protected array $hashedFields    = [];

    protected $beforeInsert = ['encryptFields'];
    protected $beforeUpdate = ['encryptFields', 'stampResolved'];
    protected $afterFind    = ['decryptFields'];

    protected function stampResolved(array $data): array
    {
        if (($data['data']['status'] ?? null) === 'resolved') {
            $data['data']['resolved_at'] = date('Y-m-d H:i:s');
        } elseif (($data['data']['status'] ?? null) === 'open') {
            $data['data']['resolved_at'] = null;
        }

        return $data;
    }
}
