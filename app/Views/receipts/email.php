<div style="font-family:Arial,sans-serif;color:#111;line-height:1.6;max-width:600px">
  <h2 style="margin-bottom:0"><?= esc(org('name')) ?></h2>
  <p>Dear <?= esc($d['donor_name']) ?>,</p>
  <p>Thank you for your generous donation of <strong><?= esc(money($d['amount'])) ?></strong> on <?= esc(fmt_date($d['donated_on'])) ?><?= $d['campaign_name'] ? ' towards <em>' . esc($d['campaign_name']) . '</em>' : '' ?>. Your support helps us continue our work in healthcare, education, vocational training and food security.</p>
  <p>Your official receipt, number <strong><?= esc($d['receipt_no']) ?></strong>, is valid for tax purposes under Section 80G. You can view or print it any time from the link our team can share with you, or simply reply to this email if you'd like a copy resent.</p>
  <p>With gratitude,<br>The <?= esc(org('name')) ?> Team</p>
</div>
