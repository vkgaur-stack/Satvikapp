<?php $title = ($mode === 'create' ? 'New ' : 'Edit ') . $def['singular']; $errors = session()->getFlashdata('errors') ?? []; ?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<a href="<?= site_url('m/' . $slug . ($mode === 'edit' ? '/' . $id : '')) ?>" class="text-body-secondary small d-inline-block mb-2"><i class="bi bi-arrow-left"></i> Back</a>

<div class="card" style="max-width:760px">
  <div class="card-header"><?= esc($title) ?></div>
  <div class="card-body">
    <?php if (! empty($errors['_'])): ?><div class="alert alert-danger py-2"><?= esc($errors['_']) ?></div><?php endif; ?>
    <form method="post" action="<?= site_url($mode === 'create' ? 'm/' . $slug : 'm/' . $slug . '/' . $id) ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <?php foreach ($fields as $f): $val = old($f['name'], $row[$f['name']] ?? '') ?? ''; $err = $errors[$f['name']] ?? null; ?>
        <div class="mb-3">
          <label class="form-label"><?= esc($f['label']) ?><?= (str_contains($f['rules'] ?? '', 'required') && $f['type'] !== 'password') ? ' <span class="text-danger">*</span>' : '' ?></label>

          <?php if ($f['type'] === 'select'): ?>
            <select name="<?= esc($f['name']) ?>" class="form-select <?= $err ? 'is-invalid' : '' ?>">
              <option value="">— Select —</option>
              <?php foreach ($f['options'] as $ov => $ol): ?><option value="<?= esc($ov) ?>" <?= (string) $val === (string) $ov ? 'selected' : '' ?>><?= esc($ol) ?></option><?php endforeach; ?>
            </select>

          <?php elseif ($f['type'] === 'fk'): ?>
            <select name="<?= esc($f['name']) ?>" class="form-select <?= $err ? 'is-invalid' : '' ?>">
              <option value="">— Select —</option>
              <?php foreach ($fkOptions[$f['name']] as $ov => $ol): ?><option value="<?= esc($ov) ?>" <?= (string) $val === (string) $ov ? 'selected' : '' ?>><?= esc($ol) ?></option><?php endforeach; ?>
            </select>
            <?php if (empty($fkOptions[$f['name']])): ?><div class="form-text text-warning">No records available yet — add one first.</div><?php endif; ?>

          <?php elseif ($f['type'] === 'textarea'): ?>
            <textarea name="<?= esc($f['name']) ?>" class="form-control <?= $err ? 'is-invalid' : '' ?>" rows="4"><?= esc($val) ?></textarea>

          <?php elseif ($f['type'] === 'checkbox'): ?>
            <div class="form-check"><input type="checkbox" name="<?= esc($f['name']) ?>" value="1" class="form-check-input" <?= $val ? 'checked' : '' ?>></div>

          <?php elseif ($f['type'] === 'file'): ?>
            <input type="file" name="<?= esc($f['name']) ?>" class="form-control <?= $err ? 'is-invalid' : '' ?>" accept="image/jpeg,image/png,application/pdf">
            <?php if ($mode === 'edit' && ! empty($row['_raw_file'][$f['name']])): ?><div class="form-text">A file is already on record. Choose a new one to replace it.</div><?php endif; ?>

          <?php elseif ($f['type'] === 'money'): ?>
            <div class="input-group"><span class="input-group-text">₹</span><input type="number" step="0.01" min="0" name="<?= esc($f['name']) ?>" class="form-control <?= $err ? 'is-invalid' : '' ?>" value="<?= esc($val) ?>"></div>

          <?php else: ?>
            <input type="<?= $f['type'] === 'password' ? 'password' : ($f['type'] === 'number' ? 'number' : ($f['type'] === 'date' ? 'date' : ($f['type'] === 'email' ? 'email' : ($f['type'] === 'tel' ? 'tel' : 'text')))) ?>"
                   name="<?= esc($f['name']) ?>" class="form-control <?= $err ? 'is-invalid' : '' ?>" value="<?= esc($f['type'] === 'password' ? '' : $val) ?>"
                   <?= isset($f['step']) ? 'step="' . esc($f['step']) . '"' : '' ?> autocomplete="<?= $f['type'] === 'password' ? 'new-password' : 'off' ?>">
          <?php endif; ?>

          <?php if ($err): ?><div class="invalid-feedback d-block"><?= esc($err) ?></div><?php endif; ?>
          <?php if (! empty($f['help'])): ?><div class="form-text"><?= esc($f['help']) ?></div><?php endif; ?>
        </div>
      <?php endforeach; ?>

      <button class="btn btn-accent"><?= $mode === 'create' ? 'Save' : 'Save changes' ?></button>
      <a href="<?= site_url('m/' . $slug . ($mode === 'edit' ? '/' . $id : '')) ?>" class="btn btn-outline-secondary">Cancel</a>
    </form>
  </div>
</div>
<?= $this->endSection() ?>
