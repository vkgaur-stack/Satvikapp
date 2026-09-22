<?php $title = $def['singular'] . ': ' . $row['_title']; ?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <a href="<?= site_url('m/' . $slug) ?>" class="text-body-secondary small"><i class="bi bi-arrow-left"></i> Back to <?= esc(strtolower($def['label'])) ?></a>
    <h2 class="h4 mt-1 mb-0"><?= esc($row['_title']) ?></h2>
  </div>
  <div class="d-flex gap-2">
    <?php foreach ($def['row_actions'] ?? [] as $ra): if (isset($ra['perm']) && ! can($ra['perm'])) { continue; } ?>
      <a class="btn btn-sm btn-outline-secondary" href="<?= site_url(str_replace('{id}', $row['id'], $ra['url'])) ?>"><i class="bi <?= esc($ra['icon']) ?>"></i> <?= esc($ra['label']) ?></a>
    <?php endforeach; ?>
    <?php if (can($slug . '.update')): ?><a class="btn btn-sm btn-outline-accent" href="<?= site_url('m/' . $slug . '/' . $row['id'] . '/edit') ?>"><i class="bi bi-pencil"></i> Edit</a><?php endif; ?>
    <?php if (can($slug . '.delete')): ?>
      <form method="post" action="<?= site_url('m/' . $slug . '/' . $row['id'] . '/delete') ?>" data-confirm="Delete this <?= esc(strtolower($def['singular'])) ?>?">
        <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">Details</div>
      <div class="card-body">
        <dl class="sd-dl">
          <?php foreach ($fields as $f): ?>
            <dt><?= esc($f['label']) ?></dt><dd><?= $row['_v'][$f['name']] ?></dd>
          <?php endforeach; ?>
        </dl>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <?php if ($slug === 'documents' && empty($row['verified_at']) && can('documents.update')): ?>
      <form method="post" action="<?= site_url('files/documents/' . $row['id'] . '/verify') ?>" class="mb-3">
        <?= csrf_field() ?><button class="btn btn-accent btn-sm w-100"><i class="bi bi-check2-circle"></i> Mark document verified</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php foreach ($related as $rel): ?>
  <div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span><?= esc($rel['label']) ?></span>
      <?php if (can($rel['def']['slug'] . '.create')): ?>
        <a class="btn btn-sm btn-outline-accent" href="<?= site_url('m/' . $rel['def']['slug'] . '/new?' . $rel['fk'] . '=' . $row['id']) ?>"><i class="bi bi-plus-lg"></i> Add</a>
      <?php endif; ?>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <?php $cols = array_values(array_filter($rel['def']['fields'], fn ($f) => ! empty($f['list']) && $f['type'] !== 'file')); ?>
        <thead><tr><?php foreach ($cols as $c): ?><th><?= esc($c['label']) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php if (empty($rel['rows'])): ?><tr><td colspan="<?= count($cols) ?>" class="text-center text-body-secondary py-3">None yet.</td></tr><?php endif; ?>
          <?php foreach ($rel['rows'] as $rr): ?>
            <tr>
              <?php foreach ($cols as $c): ?><td><a class="text-reset text-decoration-none" href="<?= site_url('m/' . $rel['def']['slug'] . '/' . $rr['id']) ?>"><?= $rr['_v'][$c['name']] ?></a></td><?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endforeach; ?>
<?= $this->endSection() ?>
