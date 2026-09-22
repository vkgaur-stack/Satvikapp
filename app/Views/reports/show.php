<?php $title = $report['title']; ?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<ul class="nav nav-pills mb-3 flex-wrap">
  <?php foreach ($tabs as $s => $label): ?>
    <li class="nav-item"><a class="nav-link <?= $s === $slug ? 'active' : '' ?>" href="<?= site_url('reports/' . $s) ?>"><?= esc($label) ?></a></li>
  <?php endforeach; ?>
</ul>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <p class="text-body-secondary mb-0"><?= esc($report['description']) ?></p>
  <?php if (can('reports.export')): ?>
    <div class="dropdown">
      <button class="btn btn-outline-accent btn-sm dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-download"></i> Export</button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="<?= site_url('reports/' . $slug . '/export?format=csv') ?>"><i class="bi bi-filetype-csv me-2"></i>CSV</a></li>
        <li><a class="dropdown-item" href="<?= site_url('reports/' . $slug . '/export?format=xlsx') ?>"><i class="bi bi-file-earmark-excel me-2"></i>Excel (.xlsx)</a></li>
        <li><a class="dropdown-item" href="<?= site_url('reports/' . $slug . '/export?format=pdf') ?>"><i class="bi bi-file-earmark-pdf me-2"></i>PDF</a></li>
      </ul>
    </div>
  <?php endif; ?>
</div>

<?php
$fmt = static function ($v, string $type) {
    if ($v === null) {
        return '<span class="text-body-secondary">—</span>';
    }
    return match ($type) {
        'money' => esc(money((float) $v)),
        'pct'   => esc(number_format((float) $v, 1)) . '%',
        'int'   => esc(number_format((int) $v)),
        'date'  => esc(fmt_date($v)),
        'label' => badge((string) $v),
        default => esc((string) $v),
    };
};
?>

<div class="row g-3 mb-4">
  <?php foreach ($report['kpis'] as [$label, $val, $type]): ?>
    <div class="col-6 col-lg-4 col-xl-2">
      <div class="card sd-stat accent">
        <div class="label"><?= esc($label) ?></div>
        <div class="value"><?= $fmt($val, $type) ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="card-header"><?= esc($report['table_title']) ?></div>
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead><tr><?php foreach ($report['headers'] as $h): ?><th><?= esc($h) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
        <?php if (empty($report['rows'])): ?><tr><td colspan="<?= count($report['headers']) ?>" class="text-center text-body-secondary py-4">No data yet.</td></tr><?php endif; ?>
        <?php foreach ($report['rows'] as $row): ?>
          <tr><?php foreach ($row as $i => $cell): ?><td><?= $fmt($cell, $report['formats'][$i]) ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>
