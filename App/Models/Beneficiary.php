<?php

namespace App\Models;

use CodeIgniter\Model;

class Beneficiary extends Model
{
    protected $table            = 'beneficiaries';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'first_name', 'last_name', 'phone', 'email', 'gender',
        'date_of_birth', 'address', 'unique_id', 'unique_id_type',
        'latitude', 'longitude', 'status', 'created_by'
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'first_name' => 'required|string|min_length[2]',
        'phone'      => 'required|string|is_unique[beneficiaries.phone]',
        'gender'     => 'required|in_list[male,female,other]',
        'email'      => 'valid_email',
    ];

    protected $validationMessages = [
        'first_name' => [
            'required'   => 'First name is required.',
            'min_length' => 'First name must be at least 2 characters.',
        ],
        'phone' => [
            'required'   => 'Phone number is required.',
            'is_unique'  => 'Phone number already exists.',
        ],
        'gender' => [
            'required' => 'Gender is required.',
        ],
    ];

    // Get full name
    public function getFullName($id)
    {
        $beneficiary = $this->find($id);
        return $beneficiary ? $beneficiary['first_name'] . ' ' . $beneficiary['last_name'] : 'N/A';
    }
}