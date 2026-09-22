<?php

namespace App\Filters;

use App\Libraries\CurrentUser;
use App\Libraries\Rbac;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** REST API: require "Authorization: Bearer <token>" (issue one via POST /api/v1/auth/token). */
class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');
        if (! preg_match('/^Bearer\s+(\S+)$/i', $header, $m)) {
            return $this->fail();
        }
        $db  = db_connect();
        $row = $db->table('api_tokens t')
            ->select('t.id AS token_id, u.*, r.name AS role_name')
            ->join('users u', 'u.id = t.user_id')
            ->join('roles r', 'r.id = u.role_id')
            ->where('t.token_hash', hash('sha256', $m[1]))
            ->where('t.expires_at >', date('Y-m-d H:i:s'))
            ->where('u.status', 'active')->where('u.deleted_at', null)
            ->get()->getRowArray();
        if (! $row) {
            return $this->fail();
        }
        $db->table('api_tokens')->where('id', $row['token_id'])->update(['last_used_at' => date('Y-m-d H:i:s')]);
        CurrentUser::set(Rbac::sessionUser($row, $row['role_name']) + ['token_id' => (int) $row['token_id']]);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function fail(): ResponseInterface
    {
        return service('response')->setStatusCode(401)->setJSON(['error' => 'Missing, invalid or expired token.']);
    }
}
