<?php

namespace App\Database\Seeders;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['name' => 'admin', 'description' => 'Full system access'],
            ['name' => 'program_manager', 'description' => 'Manage programs and beneficiaries'],
            ['name' => 'field_worker', 'description' => 'Register beneficiaries and collect data'],
            ['name' => 'donor', 'description' => 'View contributions and reports'],
            ['name' => 'auditor', 'description' => 'View reports and audit data'],
        ];

        $this->db->table('roles')->insertBatch($data);

        // Add permissions
        $permissions = [
            'view_dashboard', 'view_beneficiaries', 'create_beneficiary', 'edit_beneficiary', 'delete_beneficiary',
            'view_donors', 'create_donor', 'edit_donor', 'delete_donor',
            'view_programs', 'create_program', 'edit_program', 'delete_program',
            'view_reports', 'export_reports',
            'manage_users', 'manage_roles',
        ];

        foreach ($permissions as $perm) {
            $this->db->table('permissions')->insert(['name' => $perm]);
        }
    }
}