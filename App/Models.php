<?php

namespace App\Models;

use CodeIgniter\Model;

class User extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['name', 'email', 'phone', 'password', 'role_id', 'status'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    protected $validationRules = [
        'name'  => 'required|string|min_length[3]',
        'email' => 'required|valid_email|is_unique[users.email]',
        'password' => 'required|min_length[6]',
        'role_id' => 'required|integer',
    ];

    public function getRole()
    {
        return $this->db->table('roles')->where('id', $this->role_id)->get()->getRowArray();
    }

    public function hasPermission($permission)
    {
        return $this->db->table('role_permissions')
            ->select('role_permissions.*')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role_id', $this->role_id)
            ->where('permissions.name', $permission)
            ->countAllResults() > 0;
    }
}