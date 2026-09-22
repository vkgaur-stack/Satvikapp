<?php $title = 'Audit log'; ?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<form class="mb-3"><input type="search" name="q" class="form-control form-control-sm" style="width:260px" placeholder="Search action, resource, user…" value="<?= esc($q) ?>"></form>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead><tr><th>When</th><th>User</th><th>Action</th><th>Resource</th><th>IP</th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?><tr><td colspan="5" class="text-center text-body-secondary py-4">No entries.</td></tr><?php endif; ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td class="text-nowrap"><?= esc(fmt_date($r['created_at'], 'd M Y H:i')) ?></td>
            <td><?= esc($r['user_name'] ?? 'System') ?></td>
            <td><?= badge($r['action']) ?></td>
            <td><?= esc($r['resource_type']) ?><?= $r['resource_id'] ? ' #' . $r['resource_id'] : '' ?></td>
            <td class="text-body-secondary"><?= esc($r['ip_address']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->include('partials/pagination') ?>
<?= $this->endSection() ?>
