<?php
/**
 * dompdf template - keep CSS simple (table-based layout, no flexbox/grid) for reliable rendering.
 * @var array $report
 * @var string $slug
 */
$fmt = static function ($v, string $type) {
    if ($v === null) {
        return '&mdash;';
    }
    return match ($type) {
        'money' => esc(money((float) $v, 'Rs. ')),
        'pct'   => esc(number_format((float) $v, 1)) . '%',
        'int'   => esc(number_format((int) $v)),
        'date'  => esc(fmt_date($v)),
        'label' => esc(ucwords(str_replace('_', ' ', (string) $v))),
        default => esc((string) $v),
    };
};
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { margin: 90px 36px 60px 36px; }
  body { font-family: Helvetica, Arial, sans-serif; color: #14212F; font-size: 10.5px; }
  header { position: fixed; top: -70px; left: 0; right: 0; height: 60px; border-bottom: 2px solid #007C9C; padding-bottom: 8px; }
  footer { position: fixed; bottom: -40px; left: 0; right: 0; height: 30px; font-size: 8.5px; color: #8FA3B5; border-top: 1px solid #DCE3EA; padding-top: 6px; }
  .org { font-size: 15px; font-weight: bold; color: #007C9C; }
  .meta { color: #5B6E80; font-size: 9px; }
  h1 { font-size: 15px; margin: 0 0 2px; }
  p.desc { color: #5B6E80; margin: 0 0 16px; font-size: 9.5px; }
  table.kpis { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
  table.kpis td { width: 33.3%; padding: 8px 10px; border: 1px solid #DCE3EA; vertical-align: top; }
  table.kpis .label { font-size: 8px; text-transform: uppercase; letter-spacing: .05em; color: #5B6E80; }
  table.kpis .value { font-size: 14px; font-weight: bold; margin-top: 2px; }
  table.data { width: 100%; border-collapse: collapse; font-size: 9.5px; }
  table.data th { background: #007C9C; color: #fff; text-align: left; padding: 5px 6px; font-size: 8.5px; text-transform: uppercase; letter-spacing: .03em; }
  table.data td { padding: 5px 6px; border-bottom: 1px solid #DCE3EA; }
  table.data tr:nth-child(even) td { background: #F6F8FA; }
</style>
</head>
<body>
<header>
  <span class="org"><?= esc(org('name')) ?></span> &mdash; <?= esc($report['title']) ?> report
  <div class="meta">Generated <?= esc(date('d M Y H:i')) ?></div>
</header>
<footer>Confidential &mdash; internal report. <?= esc(org('name')) ?></footer>

<h1><?= esc($report['title']) ?></h1>
<p class="desc"><?= esc($report['description']) ?></p>

<table class="kpis">
  <?php foreach (array_chunk($report['kpis'], 3) as $chunk): ?>
    <tr>
      <?php foreach ($chunk as [$label, $val, $type]): ?>
        <td><div class="label"><?= esc($label) ?></div><div class="value"><?= $fmt($val, $type) ?></div></td>
      <?php endforeach; ?>
      <?php for ($i = count($chunk); $i < 3; $i++): ?><td style="border:none"></td><?php endfor; ?>
    </tr>
  <?php endforeach; ?>
</table>

<h2 style="font-size:12px;margin:0 0 8px;"><?= esc($report['table_title']) ?></h2>
<table class="data">
  <thead><tr><?php foreach ($report['headers'] as $h): ?><th><?= esc($h) ?></th><?php endforeach; ?></tr></thead>
  <tbody>
    <?php if (empty($report['rows'])): ?>
      <tr><td colspan="<?= count($report['headers']) ?>" style="text-align:center;color:#8FA3B5;padding:12px;">No data yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($report['rows'] as $row): ?>
      <tr><?php foreach ($row as $i => $cell): ?><td><?= $fmt($cell, $report['formats'][$i]) ?></td><?php endforeach; ?></tr>
    <?php endforeach; ?>
  </tbody>
</table>
</body>
</html>
