<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header">Change password</div>
      <div class="card-body">
        <?php $errs = session()->getFlashdata('errors'); ?>
        <?php if ($errs): ?><div class="alert alert-danger py-2"><ul class="mb-0 ps-3"><?php foreach ($errs as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="post" action="<?= site_url('account/password') ?>">
          <?= csrf_field() ?>
          <div class="mb-3"><label class="form-label">Current password</label><input type="password" name="current_password" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">New password</label><input type="password" name="new_password" class="form-control" minlength="10" required></div>
          <div class="mb-3"><label class="form-label">Confirm new password</label><input type="password" name="confirm_password" class="form-control" minlength="10" required></div>
          <button class="btn btn-accent">Update password</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
