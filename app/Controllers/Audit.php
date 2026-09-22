<?php

namespace App\Controllers;

class Audit extends BaseController
{
    public function index()
    {
        if (! can('audit.view')) {
            return $this->response->setStatusCode(403)->setBody(view('errors/forbidden'));
        }
        $db    = db_connect();
        $model = $db->table('audit_logs al')
            ->select('al.*, u.name AS user_name')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->orderBy('al.created_at', 'DESC');

        $q = trim((string) $this->request->getGet('q'));
        if ($q !== '') {
            $model->groupStart()->like('al.action', $q)->orLike('al.resource_type', $q)->orLike('u.name', $q)->groupEnd();
        }

        $rows = $model->paginate(25);

        return view('audit/index', ['rows' => $rows, 'pager' => $model, 'q' => $q]);
    }
}
