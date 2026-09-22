<?php

namespace App\Libraries;

/** Role -> permission lookups (tables: roles, permissions, role_permissions). */
class Rbac
{
    /** @return string[] permission slugs granted to the role */
    public static function permissionsForRole(int $roleId): array
    {
        $rows = db_connect()->table('role_permissions rp')
            ->select('p.slug')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', $roleId)
            ->get()->getResultArray();

        return array_column($rows, 'slug');
    }

    /** Build the array stored in the session / CurrentUser from a users row joined with its role. */
    public static function sessionUser(array $userRow, string $roleName): array
    {
        return [
            'id'      => (int) $userRow['id'],
            'name'    => $userRow['name'],
            'email'   => $userRow['email'],
            'role_id' => (int) $userRow['role_id'],
            'role'    => $roleName,
            'perms'   => self::permissionsForRole((int) $userRow['role_id']),
        ];
    }
}
