<?php $title = $def['label']; ?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <form class="d-flex gap-2 flex-wrap" method="get">
    <?php if ($def['search']): ?>
      <input type="search" name="q" class="form-control form-control-sm" style="width:220px" placeholder="Search…" value="<?= esc($q) ?>">
    <?php endif; ?>
    <?php foreach ($def['filters'] as $fname): $fd = null; foreach ($def['fields'] as $ff) { if ($ff['name'] === $fname) { $fd = $ff; } } ?>
      <?php if ($fd && $fd['type'] === 'select'): ?>
        <select name="<?= esc($fname) ?>" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
          <option value="">All <?= esc(strtolower($fd['label'])) ?></option>
          <?php foreach ($fd['options'] as $val => $lab): ?>
            <option value="<?= esc($val) ?>" <?= (service('request')->getGet($fname) == $val) ? 'selected' : '' ?>><?= esc($lab) ?></option>
          <?php endforeach; ?>
        </select>
      <?php elseif ($fname === 'dedup_flag'): ?>
        <select name="dedup_flag" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
          <option value="">All records</option>
          <option value="1" <?= (service('request')->getGet('dedup_flag') === '1') ? 'selected' : '' ?>>Flagged duplicates only</option>
        </select>
      <?php endif; ?>
    <?php endforeach; ?>
    <?php if ($def['search']): ?><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button><?php endif; ?>
  </form>
  <?php if (can($slug . '.create')): ?>
    <a href="<?= site_url('m/' . $slug . '/new') ?>" class="btn btn-accent btn-sm"><i class="bi bi-plus-lg"></i> Add <?= esc($def['singular']) ?></a>
  <?php endif; ?>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead>
        <tr>
          <?php foreach ($fields as $f): ?><th><?= esc($f['label']) ?></th><?php endforeach; ?>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="<?= count($fields) + 1 ?>" class="text-center text-body-secondary py-4">No records yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <?php foreach ($fields as $f): ?><td><a class="text-reset text-decoration-none stretched-link-off" href="<?= site_url('m/' . $slug . '/' . $r['id']) ?>"><?= $r['_v'][$f['name']] ?></a></td><?php endforeach; ?>
            <td class="text-end text-nowrap">
              <?php foreach ($def['row_actions'] ?? [] as $ra): if (isset($ra['perm']) && ! can($ra['perm'])) { continue; } ?>
                <a class="btn btn-sm btn-outline-secondary" href="<?= site_url(str_replace('{id}', $r['id'], $ra['url'])) ?>" title="<?= esc($ra['label']) ?>"><i class="bi <?= esc($ra['icon']) ?>"></i></a>
              <?php endforeach; ?>
              <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('m/' . $slug . '/' . $r['id']) ?>" title="View"><i class="bi bi-eye"></i></a>
              <?php if (can($slug . '.update')): ?><a class="btn btn-sm btn-outline-secondary" href="<?= site_url('m/' . $slug . '/' . $r['id'] . '/edit') ?>" title="Edit"><i class="bi bi-pencil"></i></a><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->include('partials/pagination') ?>
<?= $this->endSection() ?>
