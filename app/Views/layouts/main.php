<?php
$svc  = new \App\Libraries\ModuleService();
$menu = $svc->menu();
$path = trim(service('request')->getUri()->getPath(), '/');
$isActive = static fn (string $prefix): bool => $prefix === '' ? $path === '' : str_starts_with($path, $prefix);
$user = current_user();
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <title><?= esc($title ?? 'Satvikdaan') ?> · <?= esc(org('name')) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
  <script>document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('sd-theme') || 'dark');</script>
</head>
<body>
<div class="d-flex">
  <aside class="sd-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar">
    <a class="sd-brand" href="<?= site_url('/') ?>">
      <span class="mark">S</span>
      <span>Satvikdaan<small>Donors &amp; Beneficiaries</small></span>
    </a>
    <nav class="sd-nav pb-4">
      <?php if (can('dashboard.view')): ?>
        <a href="<?= site_url('/') ?>" class="<?= $isActive('') ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <?php endif; ?>
      <?php foreach ($menu as $group => $items): ?>
        <div class="sd-nav-group"><?= esc($group) ?></div>
        <?php foreach ($items as $it): ?>
          <a href="<?= site_url('m/' . $it['slug']) ?>" class="<?= $isActive('m/' . $it['slug']) ? 'active' : '' ?>"><i class="bi <?= esc($it['icon']) ?>"></i> <?= esc($it['label']) ?></a>
        <?php endforeach; ?>
      <?php endforeach; ?>
      <?php if (can('stewardship.manage') || can('reports.view') || can('audit.view')): ?>
        <div class="sd-nav-group">Insight</div>
        <?php if (can('stewardship.manage')): ?><a href="<?= site_url('stewardship') ?>" class="<?= $isActive('stewardship') ? 'active' : '' ?>"><i class="bi bi-envelope-heart"></i> Stewardship</a><?php endif; ?>
        <?php if (can('reports.view')): ?><a href="<?= site_url('reports') ?>" class="<?= $isActive('reports') ? 'active' : '' ?>"><i class="bi bi-bar-chart-line"></i> Reports</a><?php endif; ?>
        <?php if (can('audit.view')): ?><a href="<?= site_url('audit') ?>" class="<?= $isActive('audit') ? 'active' : '' ?>"><i class="bi bi-clipboard-data"></i> Audit log</a><?php endif; ?>
      <?php endif; ?>
      <div class="sd-nav-group">Public</div>
      <a href="<?= site_url('donate') ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Online giving page</a>
    </nav>
  </aside>

  <div class="sd-main">
    <header class="sd-topbar">
      <button class="btn btn-sm btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-label="Menu"><i class="bi bi-list"></i></button>
      <h1 class="h5 mb-0 flex-grow-1"><?= esc($title ?? '') ?></h1>
      <button id="theme-toggle" class="btn btn-sm btn-outline-secondary" aria-label="Switch light/dark theme" title="Switch theme"></button>
      <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i> <?= esc($user['name'] ?? '') ?></button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><span class="dropdown-item-text small text-body-secondary"><?= esc(ucwords(str_replace('_', ' ', $user['role'] ?? ''))) ?></span></li>
          <li><a class="dropdown-item" href="<?= site_url('account/password') ?>">Change password</a></li>
          <li><form action="<?= site_url('logout') ?>" method="post"><?= csrf_field() ?><button class="dropdown-item">Sign out</button></form></li>
        </ul>
      </div>
    </header>

    <main class="sd-content">
      <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $k => $cls): ?>
        <?php if ($msg = session()->getFlashdata($k)): ?><div class="alert alert-<?= $cls ?> alert-dismissible fade show" role="alert"><?= esc($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
      <?php endforeach; ?>

      <?= $this->renderSection('content') ?>
    </main>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
