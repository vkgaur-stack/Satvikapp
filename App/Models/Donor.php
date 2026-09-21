<?php

namespace App\Models;

use CodeIgniter\Model;

class Donor extends Model
{
    protected $table            = 'donors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'name', 'type', 'email', 'phone', 'address', 
        'city', 'state', 'zip_code', 'total_contribution', 'status'
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'name'  => 'required|string|min_length[3]',
        'type'  => 'required|in_list[individual,organization,corporate]',
        'email' => 'valid_email',
        'phone' => 'string',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Donor name is required.',
            'min_length' => 'Donor name must be at least 3 characters.',
        ],
    ];
}