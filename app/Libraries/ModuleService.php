<?php

namespace App\Libraries;

use CodeIgniter\HTTP\IncomingRequest;

/**
 * The engine behind the generic CRUD screens and the REST API.
 * It reads the module definitions in Config\Catalog and handles validation, field collection,
 * scoping (field workers only see their own records), masking for restricted roles, and display formatting.
 */
class ModuleService
{
    public const MASK = '••••••';

    public function all(): array
    {
        return config('Catalog')->modules;
    }

    public function def(string $slug): ?array
    {
        $m = $this->all()[$slug] ?? null;
        if ($m === null) {
            return null;
        }
        $m['slug']  = $slug;
        $m['table'] = $this->model($m)->getTable();

        return $m;
    }

    public function model(array $def): \CodeIgniter\Model
    {
        $class = $def['model'];

        return new $class();
    }

    /** Fields for a screen: list | show | create | edit */
    public function fields(array $def, string $mode): array
    {
        return array_values(array_filter($def['fields'], static function (array $f) use ($mode): bool {
            if ($mode === 'list') {
                return ! empty($f['list']) && $f['type'] !== 'file';
            }
            if ($mode === 'show') {
                return ($f['show'] ?? true) !== false;
            }
            $form = $f['form'] ?? true;

            return $form === true || $form === $mode;
        }));
    }

    public function field(array $def, string $name): ?array
    {
        foreach ($def['fields'] as $f) {
            if ($f['name'] === $name) {
                return $f;
            }
        }

        return null;
    }

    // ------------------------------------------------------------------ validation & input

    public function rules(array $def, string $mode, int $id = 0): array
    {
        $rules = [];
        foreach ($this->fields($def, $mode) as $f) {
            if ($f['type'] === 'file') {
                continue;
            }
            $r = $f['rules'] ?? 'permit_empty';
            if ($mode === 'edit' && ! empty($f['rules_edit'])) {
                $r = $f['rules_edit'];
            }
            if ($f['type'] === 'select' && ! str_contains($r, 'in_list')) {
                $r .= '|in_list[' . implode(',', array_keys($f['options'])) . ']';
            }
            if ($f['type'] === 'fk') {
                [$table] = $this->fkTarget($f);
                $r .= '|is_not_unique[' . $table . '.id]';
            }
            $rules[$f['name']] = ['label' => $f['label'], 'rules' => str_replace('{id}', (string) $id, $r)];
        }

        return $rules;
    }

    /** @return array<string,string> field => message */
    public function validate(array $def, string $mode, array $input, int $id = 0): array
    {
        $v = service('validation');
        $v->reset();
        $v->setRules($this->rules($def, $mode, $id));
        $v->run($input);

        return $v->getErrors();
    }

    /** Whitelist the posted values down to the fields this form is allowed to write. */
    public function collect(array $def, string $mode, array $input): array
    {
        $data   = [];
        $canPii = can('pii.decrypt');
        foreach ($this->fields($def, $mode) as $f) {
            $n = $f['name'];
            if ($f['type'] === 'file' || ! empty($f['virtual'])) {
                continue;
            }
            if ($f['type'] === 'checkbox') {
                $data[$n] = empty($input[$n]) ? 0 : 1;
                continue;
            }
            $val = $input[$n] ?? null;
            if (is_string($val)) {
                $val = trim($val);
            }
            if ($val === '' || $val === null) {
                // Users who cannot see PII get blank boxes on edit: blank means "keep what is stored".
                if ($f['type'] === 'password' || ($mode === 'edit' && ($f['mask'] ?? null) === 'pii' && ! $canPii)) {
                    continue;
                }
                $data[$n] = null;
                continue;
            }
            $data[$n] = $val;
        }

        return $data;
    }

    /** Move validated uploads into encrypted storage. @return array<string,string> errors */
    public function storeFiles(array $def, IncomingRequest $req, array &$data, string $mode): array
    {
        $errors = [];
        foreach ($def['fields'] as $f) {
            if ($f['type'] !== 'file') {
                continue;
            }
            $form = $f['form'] ?? true;
            if ($form !== true && $form !== $mode) {
                continue;
            }
            $file = $req->getFile($f['name']);
            if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
                if ($mode === 'create' && ! empty($f['required'])) {
                    $errors[$f['name']] = 'Please choose a file.';
                }
                continue;
            }
            if (! $file->isValid()) {
                $errors[$f['name']] = $file->getErrorString();
                continue;
            }
            if ($file->getSize() > SecureFiles::MAX_BYTES) {
                $errors[$f['name']] = 'File is larger than 5 MB.';
                continue;
            }
            if (! in_array($file->getMimeType(), SecureFiles::ALLOWED, true)) {
                $errors[$f['name']] = 'Only JPG, PNG or PDF files are accepted.';
                continue;
            }
            $meta  = SecureFiles::store($file, $def['slug']);
            $store = $f['store'] ?? ['path' => $f['name']];
            $data[$store['path']] = $meta['path'];
            foreach (['name', 'mime', 'size'] as $k) {
                if (isset($store[$k])) {
                    $data[$store[$k]] = $meta[$k];
                }
            }
        }

        return $errors;
    }

    // ------------------------------------------------------------------ querying

    private function prepare(\CodeIgniter\Model $model, array $def): void
    {
        if (! empty($def['select'])) {
            $model->select(implode(', ', $def['select']), false);
        }
        // Field workers only ever see the records they created themselves.
        if (CurrentUser::role() === 'field_worker' && ! empty($def['owner_field'])) {
            $model->where($model->getTable() . '.' . $def['owner_field'], CurrentUser::id());
        }
    }

    /** @return array{rows:array,pager:mixed,q:string} */
    public function paginate(array $def, array $get, int $perPage = 15): array
    {
        $model = $this->model($def);
        $this->prepare($model, $def);
        $table = $model->getTable();

        $q = trim((string) ($get['q'] ?? ''));
        if ($q !== '' && ! empty($def['search'])) {
            $cols = array_values(array_filter($def['search'], function (string $col) use ($def): bool {
                $f = $this->field($def, $col);
                $mask = $f['mask'] ?? null;

                return ! ($mask === 'identity' && ! can('identity.view')) && ! ($mask === 'pii' && ! can('pii.decrypt'));
            }));
            if ($cols) {
                $model->groupStart();
                foreach ($cols as $i => $col) {
                    $i === 0 ? $model->like($table . '.' . $col, $q) : $model->orLike($table . '.' . $col, $q);
                }
                $model->groupEnd();
            }
        }
        foreach ($def['filters'] ?? [] as $name) {
            $val = $get[$name] ?? '';
            if ($val !== '' && is_scalar($val)) {
                $model->where($table . '.' . $name, $val);
            }
        }
        [$col, $dir] = $def['order'] ?? ['id', 'DESC'];
        $model->orderBy($table . '.' . $col, $dir)->orderBy($table . '.id', 'DESC');

        $rows = $model->paginate(max(1, min(100, $perPage)));

        return ['rows' => $rows, 'pager' => $model->pager, 'q' => $q];
    }

    public function find(array $def, int $id): ?array
    {
        $model = $this->model($def);
        $this->prepare($model, $def);

        return $model->where($model->getTable() . '.id', $id)->first();
    }

    /** Rows of a module that belong to a parent record (used for the related lists on detail pages). */
    public function children(array $def, string $fk, int $parentId, int $limit = 50): array
    {
        $model = $this->model($def);
        $this->prepare($model, $def);

        return $model->where($model->getTable() . '.' . $fk, $parentId)->orderBy($model->getTable() . '.id', 'DESC')->findAll($limit);
    }

    /**
     * Field workers may only link records to beneficiaries (etc.) that they created themselves.
     * @return array<string,string> errors
     */
    public function scopeErrors(array $def, array $data): array
    {
        $errors = [];
        if (CurrentUser::role() !== 'field_worker') {
            return $errors;
        }
        foreach ($def['fields'] as $f) {
            if ($f['type'] !== 'fk' || ! isset($f['fk']['module']) || empty($data[$f['name']])) {
                continue;
            }
            $target = $this->def($f['fk']['module']);
            if (empty($target['owner_field'])) {
                continue;
            }
            $ok = db_connect()->table($target['table'])->where('id', $data[$f['name']])->where($target['owner_field'], CurrentUser::id())->where('deleted_at', null)->countAllResults();
            if (! $ok) {
                $errors[$f['name']] = 'That record is not assigned to you.';
            }
        }

        return $errors;
    }

    // ------------------------------------------------------------------ masking & display

    /** Hide what this user is not entitled to see. */
    public function maskRow(array $def, array $row): array
    {
        foreach ($def['fields'] as $f) {
            $mask = $f['mask'] ?? null;
            $n    = $f['name'];
            if ($mask === null || ! array_key_exists($n, $row) || $row[$n] === null || $row[$n] === '') {
                continue;
            }
            if ($mask === 'pii' && ! can('pii.decrypt')) {
                $row[$n] = self::MASK;
            } elseif ($mask === 'identity' && ! can('identity.view')) {
                $row[$n] = $def['singular'] . ' #' . $row['id'];
            }
        }

        return $row;
    }

    /** Row as returned by the REST API: masked, no internal columns. */
    public function apiRow(array $def, array $row): array
    {
        $row = $this->maskRow($def, $row);
        foreach ($def['hidden'] ?? [] as $h) {
            unset($row[$h]);
        }
        unset($row['_v'], $row['deleted_at']);

        return $row;
    }

    /** Add $row['_v'][field] = display HTML (safe to echo) and $row['_title']. */
    public function decorate(array $def, array $rows, string $mode = 'list'): array
    {
        $fields = $this->fields($def, $mode);
        $maps   = $this->resolveFks($fields, $rows);
        foreach ($rows as &$r) {
            $r['_raw_file'] = [];
            foreach ($def['fields'] as $f) {
                if ($f['type'] === 'file') {
                    $r['_raw_file'][$f['name']] = ! empty($r[$f['name']]);
                }
            }
            $r = $this->maskRow($def, $r);
            $r['_v'] = [];
            foreach ($fields as $f) {
                $r['_v'][$f['name']] = $this->cell($def, $f, $r, $maps, $mode);
            }
            $r['_title'] = ! empty($r[$def['title']]) && $def['title'] !== 'id' ? (string) $r[$def['title']] : $def['singular'] . ' #' . $r['id'];
        }
        unset($r);

        return $rows;
    }

    private function cell(array $def, array $f, array $r, array $maps, string $mode): string
    {
        $n   = $f['name'];
        $val = $r[$n] ?? null;
        if ($f['type'] === 'file') {
            $allowed = ($def['slug'] !== 'documents' || can('pii.decrypt')) && can($def['slug'] . '.view');

            return ! empty($r['_raw_file'][$n]) && $allowed ? '<a href="' . site_url('files/' . $def['slug'] . '/' . $r['id']) . '"><i class="bi bi-paperclip"></i> Open file</a>' : '—';
        }
        if ($val === null || $val === '') {
            return '<span class="text-body-secondary">—</span>';
        }
        if ($val === self::MASK) {
            return '<span class="text-body-secondary" title="Hidden for your role">' . self::MASK . '</span>';
        }

        switch ($f['type']) {
            case 'select':
                return badge((string) $val, $f['options'][$val] ?? null);

            case 'money':
                return esc(money($val));

            case 'date':
                return esc(fmt_date($val));

            case 'checkbox':
                return $val ? badge('yes', 'Yes') : '<span class="text-body-secondary">No</span>';

            case 'fk':
                $label = $maps[$n][$val] ?? ('#' . $val);
                if (isset($f['fk']['module']) && can($f['fk']['module'] . '.view')) {
                    return '<a href="' . site_url('m/' . $f['fk']['module'] . '/' . $val) . '">' . esc($label) . '</a>';
                }

                return esc($label);

            case 'textarea':
                $t = (string) $val;
                if ($mode === 'list' && mb_strlen($t) > 70) {
                    $t = mb_substr($t, 0, 70) . '…';
                }

                return nl2br(esc($t));

            default:
                if ($n === 'id' || $f['type'] === 'number') {
                    return esc((string) $val);
                }

                return esc((string) $val);
        }
    }

    /** [table, title column, mask type of the title, singular label] */
    private function fkTarget(array $f): array
    {
        if (isset($f['fk']['module'])) {
            $d    = $this->def($f['fk']['module']);
            $tf   = $this->field($d, $d['title']);

            return [$d['table'], $d['title'], $tf['mask'] ?? null, $d['singular']];
        }

        return [$f['fk']['table'], $f['fk']['title'], null, ''];
    }

    private function resolveFks(array $fields, array $rows): array
    {
        $maps = [];
        foreach ($fields as $f) {
            if ($f['type'] !== 'fk') {
                continue;
            }
            $ids = array_values(array_unique(array_filter(array_column($rows, $f['name']))));
            $maps[$f['name']] = [];
            if (! $ids) {
                continue;
            }
            [$table, $title, $mask, $singular] = $this->fkTarget($f);
            $res = db_connect()->table($table)->select("id, {$title} AS t")->whereIn('id', $ids)->get()->getResultArray();
            foreach ($res as $r) {
                $maps[$f['name']][$r['id']] = ($mask === 'identity' && ! can('identity.view')) ? $singular . ' #' . $r['id'] : (string) $r['t'];
            }
        }

        return $maps;
    }

    /** id => label options for a foreign-key dropdown (respects field-worker scope and masking). */
    public function fkOptions(array $f): array
    {
        [$table, $title, $mask, $singular] = $this->fkTarget($f);
        $b = db_connect()->table($table)->select("id, {$title} AS t");
        if (isset($f['fk']['module'])) {
            $b->where('deleted_at', null);
            $d = $this->def($f['fk']['module']);
            if (CurrentUser::role() === 'field_worker' && ! empty($d['owner_field'])) {
                $b->where($d['owner_field'], CurrentUser::id());
            }
        }
        $out = [];
        foreach ($b->orderBy('t', 'ASC')->limit(1000)->get()->getResultArray() as $r) {
            $out[$r['id']] = ($mask === 'identity' && ! can('identity.view')) ? $singular . ' #' . $r['id'] : (string) $r['t'];
        }

        return $out;
    }

    /** Which menu items the current user may see, grouped. */
    public function menu(): array
    {
        $menu = [];
        foreach ($this->all() as $slug => $m) {
            if (can($slug . '.view')) {
                $menu[$m['group']][] = ['slug' => $slug, 'label' => $m['label'], 'icon' => $m['icon']];
            }
        }

        return $menu;
    }
}
