<?php

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\CurrentUser;
use App\Libraries\ModuleService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * One controller serves every module in Config\Catalog:  /m/<module>, /m/<module>/new, /m/<module>/<id>, ...
 */
class Crud extends BaseController
{
    private ModuleService $svc;

    public function __construct()
    {
        $this->svc = new ModuleService();
    }

    private function def(string $slug): array
    {
        $def = $this->svc->def($slug);
        if ($def === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $def;
    }

    private function forbidden()
    {
        return $this->response->setStatusCode(403)->setBody(view('errors/forbidden'));
    }

    public function index(string $slug)
    {
        $def = $this->def($slug);
        if (! can($slug . '.view')) {
            return $this->forbidden();
        }
        $result = $this->svc->paginate($def, $this->request->getGet());

        return view('crud/index', [
            'slug'   => $slug,
            'def'    => $def,
            'rows'   => $this->svc->decorate($def, $result['rows'], 'list'),
            'pager'  => $result['pager'],
            'q'      => $result['q'],
            'fields' => $this->svc->fields($def, 'list'),
        ]);
    }

    public function show(string $slug, int $id)
    {
        $def = $this->def($slug);
        if (! can($slug . '.view')) {
            return $this->forbidden();
        }
        $row = $this->svc->find($def, $id);
        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }
        if (! empty($def['pii'])) {
            Audit::log('view', $slug, $id);
        }

        $related = [];
        foreach ($def['related'] ?? [] as $rel) {
            $rd = $this->svc->def($rel['module']);
            if (! $rd || ! can($rel['module'] . '.view')) {
                continue;
            }
            $related[] = [
                'def'   => $rd,
                'label' => $rel['label'],
                'fk'    => $rel['fk'],
                'rows'  => $this->svc->decorate($rd, $this->svc->children($rd, $rel['fk'], $id), 'list'),
            ];
        }

        return view('crud/show', [
            'slug'    => $slug,
            'def'     => $def,
            'row'     => $this->svc->decorate($def, [$row], 'show')[0],
            'fields'  => $this->svc->fields($def, 'show'),
            'related' => $related,
        ]);
    }

    public function new(string $slug)
    {
        $def = $this->def($slug);
        if (! can($slug . '.create')) {
            return $this->forbidden();
        }
        $prefill = [];
        foreach ($this->svc->fields($def, 'create') as $f) {
            if ($f['type'] === 'fk' && $this->request->getGet($f['name'])) {
                $prefill[$f['name']] = $this->request->getGet($f['name']);
            }
        }

        return view('crud/form', $this->formData($def, 'create', $prefill));
    }

    public function edit(string $slug, int $id)
    {
        $def = $this->def($slug);
        if (! can($slug . '.update')) {
            return $this->forbidden();
        }
        $row = $this->svc->find($def, $id);
        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }
        if (! empty($def['pii'])) {
            Audit::log('view', $slug, $id);
        }

        return view('crud/form', $this->formData($def, 'edit', $this->svc->maskRow($def, $row), $id));
    }

    public function create(string $slug)
    {
        $def = $this->def($slug);
        if (! can($slug . '.create')) {
            return $this->forbidden();
        }
        $input  = $this->request->getPost();
        $errors = $this->svc->validate($def, 'create', $input);
        $data   = $this->svc->collect($def, 'create', $input);
        $errors += $this->svc->scopeErrors($def, $data);
        if (! $errors) {
            $errors += $this->svc->storeFiles($def, $this->request, $data, 'create');
        }
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }
        if (! empty($def['owner_field']) && CurrentUser::id()) {
            $data[$def['owner_field']] = CurrentUser::id();
        }

        $model = $this->svc->model($def);
        try {
            $id = $model->insert($data, true);
        } catch (\Throwable $e) {
            log_message('error', 'Create ' . $slug . ' failed: ' . $e->getMessage());
            $id = false;
        }
        if (! $id) {
            return redirect()->back()->withInput()->with('errors', ['_' => 'Could not save the record. Please check the details and try again.']);
        }
        Audit::log('create', $slug, (int) $id, null, $data);

        $redirect = redirect()->to(site_url(isset($def['redirect_after_create']) ? str_replace('{id}', (string) $id, $def['redirect_after_create']) : 'm/' . $slug . '/' . $id))
            ->with('success', $def['singular'] . ' saved.');
        if ($slug === 'beneficiaries') {
            $saved = $model->find($id);
            if (! empty($saved['dedup_flag'])) {
                $redirect->with('warning', 'Saved, but this looks like a possible duplicate of beneficiary #' . (int) $saved['dedup_of'] . '. A Program Manager will review it.');
            }
        }
        if ($slug === 'enrollments') {
            $saved = $model->find($id);
            $redirect->with('info', 'Eligibility screening: ' . ucfirst((string) $saved['status']) . ' — ' . $saved['eligibility_notes']);
        }

        return $redirect;
    }

    public function update(string $slug, int $id)
    {
        $def = $this->def($slug);
        if (! can($slug . '.update')) {
            return $this->forbidden();
        }
        $row = $this->svc->find($def, $id);
        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }
        $input  = $this->request->getPost();
        $errors = $this->svc->validate($def, 'edit', $input, $id);
        $data   = $this->svc->collect($def, 'edit', $input);
        $errors += $this->svc->scopeErrors($def, $data);
        if (! $errors) {
            $errors += $this->svc->storeFiles($def, $this->request, $data, 'edit');
        }
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        try {
            $this->svc->model($def)->update($id, $data);
        } catch (\Throwable $e) {
            log_message('error', 'Update ' . $slug . ' failed: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('errors', ['_' => 'Could not save the changes.']);
        }
        $before = $after = [];
        foreach ($data as $k => $v) {
            if (($row[$k] ?? null) != $v) {
                $before[$k] = $row[$k] ?? null;
                $after[$k]  = $v;
            }
        }
        Audit::log('update', $slug, $id, $before, $after);

        return redirect()->to(site_url('m/' . $slug . '/' . $id))->with('success', $def['singular'] . ' updated.');
    }

    public function delete(string $slug, int $id)
    {
        $def = $this->def($slug);
        if (! can($slug . '.delete')) {
            return $this->forbidden();
        }
        if (! $this->svc->find($def, $id)) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->svc->model($def)->delete($id);
        Audit::log('delete', $slug, $id);

        return redirect()->to(site_url('m/' . $slug))->with('success', $def['singular'] . ' deleted (it can be restored from the database if needed).');
    }

    private function formData(array $def, string $mode, array $row, int $id = 0): array
    {
        $fields = $this->svc->fields($def, $mode);
        $opts   = [];
        foreach ($fields as $f) {
            if ($f['type'] === 'fk') {
                $opts[$f['name']] = $this->svc->fkOptions($f);
            }
        }

        return ['slug' => $def['slug'], 'def' => $def, 'mode' => $mode, 'id' => $id, 'row' => $row, 'fields' => $fields, 'fkOptions' => $opts];
    }
}
