<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in · Satvikdaan</title>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
  <script>document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('sd-theme') || 'dark');</script>
</head>
<body>
<div class="sd-auth">
  <div class="card shadow-sm">
    <div class="card-body p-4 p-md-5">
      <div class="text-center mb-4">
        <span class="mark d-inline-flex align-items-center justify-content-center" style="width:44px;height:44px;border-radius:12px;background:#00D9FF;color:#04222B;font-weight:700;font-size:1.3rem;">S</span>
        <h1 class="h4 mt-3 mb-0">Satvikdaan</h1>
        <p class="text-body-secondary small mb-0">Donor &amp; Beneficiary Management</p>
      </div>
      <?php if ($e = session()->getFlashdata('error')): ?><div class="alert alert-danger py-2"><?= esc($e) ?></div><?php endif; ?>
      <form method="post" action="<?= site_url('login') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= esc(old('email')) ?>" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-accent w-100" type="submit">Sign in</button>
      </form>
      <p class="text-center text-body-secondary small mt-4 mb-0">Demo logins: admin@satvikdaan.org / Admin@12345<br>pm · field · auditor · donor @satvikdaan.org / Demo@12345</p>
    </div>
  </div>
</div>
</body>
</html>
