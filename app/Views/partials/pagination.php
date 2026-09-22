<?php
/** @var \CodeIgniter\Pager\Pager $pager */
$pages = $pager->getPageCount();
$cur   = $pager->getCurrentPage();
if ($pages > 1):
    $from = max(1, $cur - 2); $to = min($pages, $cur + 2);
?>
<nav aria-label="Pagination" class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
  <small class="text-body-secondary"><?= number_format($pager->getTotal()) ?> records</small>
  <ul class="pagination pagination-sm mb-0">
    <li class="page-item <?= $cur <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= $cur > 1 ? esc($pager->getPageURI($cur - 1)) : '#' ?>">Previous</a></li>
    <?php for ($i = $from; $i <= $to; $i++): ?>
      <li class="page-item <?= $i === $cur ? 'active' : '' ?>"><a class="page-link" href="<?= esc($pager->getPageURI($i)) ?>"><?= $i ?></a></li>
    <?php endfor; ?>
    <li class="page-item <?= $cur >= $pages ? 'disabled' : '' ?>"><a class="page-link" href="<?= $cur < $pages ? esc($pager->getPageURI($cur + 1)) : '#' ?>">Next</a></li>
  </ul>
</nav>
<?php endif; ?>
