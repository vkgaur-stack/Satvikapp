<?php

namespace App\Models;

class DocumentModel extends BaseModel
{
    protected $table         = 'documents';
    protected $allowedFields = ['beneficiary_id', 'document_type', 'title', 'file_path', 'original_name', 'mime_type', 'file_size', 'verified_by', 'verified_at', 'created_by'];
}
