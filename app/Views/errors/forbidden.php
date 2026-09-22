<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="text-center py-5">
  <div class="display-4 mb-2"><i class="bi bi-shield-lock"></i></div>
  <h2 class="h4">You don't have access to this page</h2>
  <p class="text-body-secondary">Your role doesn't include this permission. If you think it should, ask an administrator.</p>
  <a class="btn btn-accent" href="<?= site_url('/') ?>">Back to home</a>
</div>
<?= $this->endSection() ?>
