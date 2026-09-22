<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\Audit;
use App\Libraries\CurrentUser;
use App\Libraries\ModuleService;

/**
 * Generic REST endpoints for every module flagged "api" in Config\Catalog:
 *   GET    /api/v1/{module}?q=&page=&per_page=     GET /api/v1/{module}/{id}
 *   POST   /api/v1/{module}                         PUT/PATCH /api/v1/{module}/{id}      DELETE /api/v1/{module}/{id}
 * File uploads (documents, proof of delivery) are done through the web app.
 */
class Resource extends BaseController
{
    private ModuleService $svc;

    public function __construct()
    {
        $this->svc = new ModuleService();
    }

    private function def(string $slug, string $perm): array|\CodeIgniter\HTTP\ResponseInterface
    {
        $def = $this->svc->def($slug);
        if (! $def || empty($def['api'])) {
            return $this->err('Unknown resource.', 404);
        }
        if (! can($slug . '.' . $perm)) {
            return $this->err('Forbidden.', 403);
        }

        return $def;
    }

    private function err(string $msg, int $code, array $extra = [])
    {
        return $this->response->setStatusCode($code)->setJSON(['error' => $msg] + $extra);
    }

    public function index(string $slug)
    {
        $def = $this->def($slug, 'view');
        if (! is_array($def)) {
            return $def;
        }
        $res = $this->svc->paginate($def, $this->request->getGet(), (int) ($this->request->getGet('per_page') ?: 20));

        return $this->response->setJSON([
            'data' => array_map(fn ($r) => $this->svc->apiRow($def, $r), $res['rows']),
            'meta' => ['page' => $res['pager']->getCurrentPage(), 'pages' => $res['pager']->getPageCount(), 'total' => $res['pager']->getTotal()],
        ]);
    }

    public function show(string $slug, int $id)
    {
        $def = $this->def($slug, 'view');
        if (! is_array($def)) {
            return $def;
        }
        $row = $this->svc->find($def, $id);
        if (! $row) {
            return $this->err('Not found.', 404);
        }
        if (! empty($def['pii'])) {
            Audit::log('view', $slug, $id);
        }

        return $this->response->setJSON(['data' => $this->svc->apiRow($def, $row)]);
    }

    public function create(string $slug)
    {
        $def = $this->def($slug, 'create');
        if (! is_array($def)) {
            return $def;
        }
        foreach ($def['fields'] as $f) {
            if ($f['type'] === 'file' && ! empty($f['required'])) {
                return $this->err('This resource requires a file upload - use the web app.', 422);
            }
        }
        $in     = $this->request->getJSON(true) ?? [];
        $errors = $this->svc->validate($def, 'create', $in);
        $data   = $this->svc->collect($def, 'create', $in);
        $errors += $this->svc->scopeErrors($def, $data);
        if ($errors) {
            return $this->err('Validation failed.', 422, ['fields' => $errors]);
        }
        if (! empty($def['owner_field'])) {
            $data[$def['owner_field']] = CurrentUser::id();
        }
        $id = $this->svc->model($def)->insert($data, true);
        if (! $id) {
            return $this->err('Could not save.', 500);
        }
        Audit::log('create', $slug, (int) $id, null, $data);

        return $this->response->setStatusCode(201)->setJSON(['data' => $this->svc->apiRow($def, $this->svc->find($def, (int) $id))]);
    }

    public function update(string $slug, int $id)
    {
        $def = $this->def($slug, 'update');
        if (! is_array($def)) {
            return $def;
        }
        $row = $this->svc->find($def, $id);
        if (! $row) {
            return $this->err('Not found.', 404);
        }
        // Partial updates: validate only the fields that were sent.
        $in = $this->request->getJSON(true) ?? [];
        $def['fields'] = array_values(array_filter($def['fields'], static fn ($f) => array_key_exists($f['name'], $in)));
        $errors = $this->svc->validate($def, 'edit', $in, $id);
        $data   = $this->svc->collect($def, 'edit', $in);
        $errors += $this->svc->scopeErrors($def, $data);
        if ($errors) {
            return $this->err('Validation failed.', 422, ['fields' => $errors]);
        }
        if ($data) {
            $this->svc->model($def)->update($id, $data);
            Audit::log('update', $slug, $id, null, $data);
        }

        return $this->response->setJSON(['data' => $this->svc->apiRow($def, $this->svc->find($def, $id))]);
    }

    public function delete(string $slug, int $id)
    {
        $def = $this->def($slug, 'delete');
        if (! is_array($def)) {
            return $def;
        }
        if (! $this->svc->find($def, $id)) {
            return $this->err('Not found.', 404);
        }
        $this->svc->model($def)->delete($id);
        Audit::log('delete', $slug, $id);

        return $this->response->setJSON(['ok' => true]);
    }
}
