<?php $title = 'Receipt ' . ($d['receipt_no'] ?: '(not yet issued)'); ?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 no-print">
  <a href="<?= site_url('m/donations/' . $d['id']) ?>" class="text-body-secondary small"><i class="bi bi-arrow-left"></i> Back to donation</a>
  <div class="d-flex gap-2">
    <?php if (can('receipts.issue')): ?>
      <?php if ($d['receipt_status'] !== 'issued'): ?>
        <form method="post" action="<?= site_url('receipts/' . $d['id'] . '/issue') ?>"><?= csrf_field() ?><button class="btn btn-accent btn-sm" <?= $d['payment_status'] !== 'captured' ? 'disabled title="Payment not yet captured"' : '' ?>><i class="bi bi-receipt"></i> Issue receipt</button></form>
      <?php else: ?>
        <form method="post" action="<?= site_url('receipts/' . $d['id'] . '/email') ?>"><?= csrf_field() ?><button class="btn btn-outline-accent btn-sm" <?= empty($d['donor_email']) ? 'disabled title="No email on file"' : '' ?>><i class="bi bi-envelope"></i> Email to donor</button></form>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print / Save PDF</button>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<div class="receipt-sheet shadow-sm">
  <?php if ($d['receipt_status'] !== 'issued'): ?><div class="mb-3"><span class="watermark">DRAFT — NOT YET ISSUED</span></div><?php endif; ?>
  <div class="d-flex justify-content-between align-items-start mb-4">
    <div>
      <h2 class="h4 mb-0"><?= esc(org('name')) ?></h2>
      <div class="small"><?= nl2br(esc(org('address'))) ?></div>
      <div class="small"><?= esc(org('email')) ?> <?= org('phone') ? '· ' . esc(org('phone')) : '' ?></div>
      <?php if (org('pan')): ?><div class="small">PAN: <?= esc(org('pan')) ?><?= org('reg_80g') ? ' · 80G Reg: ' . esc(org('reg_80g')) : '' ?></div><?php endif; ?>
    </div>
    <div class="text-end">
      <h4 class="mb-0">Donation Receipt</h4>
      <div class="small">No: <?= esc($d['receipt_no'] ?: '—') ?></div>
      <div class="small">Date: <?= esc(fmt_date($d['receipt_issued_at'] ?: $d['donated_on'])) ?></div>
    </div>
  </div>

  <table class="w-100 mb-4">
    <tr><td style="width:160px" class="text-secondary">Received from</td><td><strong><?= esc($d['donor_name']) ?></strong></td></tr>
    <?php if ($d['donor_address']): ?><tr><td class="text-secondary">Address</td><td><?= esc($d['donor_address']) ?><?= $d['donor_city'] ? ', ' . esc($d['donor_city']) : '' ?><?= $d['donor_state'] ? ', ' . esc($d['donor_state']) : '' ?></td></tr><?php endif; ?>
    <?php if ($d['donor_pan']): ?><tr><td class="text-secondary">PAN</td><td><?= esc($d['donor_pan']) ?></td></tr><?php endif; ?>
    <tr><td class="text-secondary">Amount</td><td><strong style="font-size:1.2rem"><?= esc(money($d['amount'])) ?></strong></td></tr>
    <tr><td class="text-secondary">Amount in words</td><td><?= esc(amount_in_words((float) $d['amount'])) ?></td></tr>
    <tr><td class="text-secondary">Payment method</td><td><?= esc(ucwords(str_replace('_', ' ', $d['payment_method']))) ?><?= $d['reference_no'] ? ' (Ref: ' . esc($d['reference_no']) . ')' : '' ?></td></tr>
    <tr><td class="text-secondary">Purpose / Campaign</td><td><?= esc($d['campaign_name'] ?: 'General fund') ?></td></tr>
    <tr><td class="text-secondary">Date of donation</td><td><?= esc(fmt_date($d['donated_on'])) ?></td></tr>
  </table>

  <p class="small">This donation qualifies for tax deduction under Section 80G of the Income Tax Act, subject to applicable limits. Please retain this receipt for your records.</p>
  <div class="mt-5 pt-4 d-flex justify-content-end">
    <div class="text-center"><div style="border-top:1px solid #333;width:220px;padding-top:.4rem">Authorised signatory</div></div>
  </div>
</div>
<?= $this->endSection() ?>
