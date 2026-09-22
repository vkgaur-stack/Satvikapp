<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\Audit;
use App\Libraries\CurrentUser;

/** POST /api/v1/auth/token  {email, password, name?}  ->  bearer token (valid 30 days). */
class Auth extends BaseController
{
    public function token()
    {
        if (service('throttler')->check('apitoken_' . md5($this->request->getIPAddress()), 10, MINUTE) === false) {
            return $this->response->setStatusCode(429)->setJSON(['error' => 'Too many attempts.']);
        }
        $in   = $this->request->getJSON(true) ?? [];
        $user = db_connect()->table('users u')->select('u.*, r.name AS role_name')->join('roles r', 'r.id = u.role_id')
            ->where('u.email', strtolower(trim((string) ($in['email'] ?? ''))))->where('u.deleted_at', null)->get()->getRowArray();
        $hash = $user['password_hash'] ?? '$2y$10$usesomesillystringforsaltfakehashfakehashfakehashfakehashfa';
        if (! password_verify((string) ($in['password'] ?? ''), $hash) || ! $user || $user['status'] !== 'active') {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Invalid credentials.']);
        }
        $plain   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        db_connect()->table('api_tokens')->insert([
            'user_id' => $user['id'], 'token_hash' => hash('sha256', $plain), 'name' => substr((string) ($in['name'] ?? 'api'), 0, 80),
            'expires_at' => $expires, 'created_at' => date('Y-m-d H:i:s'),
        ]);
        Audit::log('api_token', 'users', (int) $user['id'], null, null, (int) $user['id']);

        return $this->response->setJSON(['token' => $plain, 'token_type' => 'Bearer', 'expires_at' => $expires, 'user' => ['id' => (int) $user['id'], 'name' => $user['name'], 'role' => $user['role_name']]]);
    }

    public function me()
    {
        $u = CurrentUser::get();

        return $this->response->setJSON(['id' => $u['id'], 'name' => $u['name'], 'email' => $u['email'], 'role' => $u['role'], 'permissions' => $u['perms']]);
    }

    public function revoke()
    {
        db_connect()->table('api_tokens')->where('id', CurrentUser::get()['token_id'] ?? 0)->delete();

        return $this->response->setJSON(['ok' => true]);
    }
}
