<?php

namespace App\Models;

class UserModel extends BaseModel
{
    protected $table         = 'users';
    protected $allowedFields = ['role_id', 'name', 'email', 'phone', 'password', 'password_hash', 'status', 'last_login_at'];
    protected $beforeInsert  = ['hashPassword'];
    protected $beforeUpdate  = ['hashPassword'];

    /** The form field is "password"; only the bcrypt hash is ever stored. */
    protected function hashPassword(array $data): array
    {
        if (! empty($data['data']['password'])) {
            $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
        }
        unset($data['data']['password']);

        return $data;
    }
}
