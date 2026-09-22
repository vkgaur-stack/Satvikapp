<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <title>Donate · <?= esc(org('name')) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
  <script>document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('sd-theme') || 'dark');</script>
</head>
<body>
<div class="sd-auth">
  <div class="card shadow-sm" style="max-width:480px">
    <div class="card-body p-4 p-md-5">
      <div class="text-center mb-4">
        <span style="width:44px;height:44px;border-radius:12px;background:#00D9FF;color:#04222B;font-weight:700;font-size:1.3rem;display:inline-flex;align-items:center;justify-content:center;">S</span>
        <h1 class="h4 mt-3 mb-1"><?= esc(org('name')) ?></h1>
        <p class="text-body-secondary small mb-0">Support healthcare, education, vocational training and food programs.</p>
      </div>

      <?php if (! $configured): ?>
        <div class="alert alert-warning">Online giving isn't switched on yet. Please contact us directly to donate.</div>
      <?php else: ?>
        <div id="donate-msg"></div>
        <form id="donate-form">
          <div class="mb-3"><label class="form-label">Your name</label><input required name="name" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Email</label><input required type="email" name="email" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Phone (optional)</label><input name="phone" class="form-control"></div>
          <div class="mb-3"><label class="form-label">PAN (optional, for 80G receipt)</label><input name="pan" class="form-control" style="text-transform:uppercase" maxlength="10" placeholder="ABCDE1234F"></div>
          <div class="mb-3">
            <label class="form-label">Campaign</label>
            <select name="campaign_id" class="form-select">
              <option value="">General fund</option>
              <?php foreach ($campaigns as $c): ?><option value="<?= esc($c['id']) ?>"><?= esc($c['name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Amount (INR)</label>
            <div class="btn-group w-100 mb-2" role="group">
              <?php foreach ([500, 1000, 2500, 5000] as $a): ?><button type="button" class="btn btn-outline-secondary amt-btn" data-amt="<?= $a ?>">₹<?= number_format($a) ?></button><?php endforeach; ?>
            </div>
            <input required type="number" min="1" max="500000" step="1" name="amount" id="amount" class="form-control" placeholder="Enter amount">
          </div>
          <button class="btn btn-accent w-100" id="donate-btn" type="submit"><i class="bi bi-heart-fill"></i> Donate now</button>
          <p class="text-body-secondary small mt-3 mb-0">Payments are processed securely by Razorpay. You will receive an emailed 80G tax receipt once the payment is confirmed.</p>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<script>
document.querySelectorAll('.amt-btn').forEach(b => b.addEventListener('click', () => { document.getElementById('amount').value = b.dataset.amt; }));

const form = document.getElementById('donate-form');
if (form) form.addEventListener('submit', async function (e) {
  e.preventDefault();
  const btn = document.getElementById('donate-btn');
  const msg = document.getElementById('donate-msg');
  msg.innerHTML = '';
  btn.disabled = true; btn.innerHTML = 'Please wait…';
  try {
    const fd = Object.fromEntries(new FormData(form).entries());
    const order = await sdPost('<?= site_url('donate/order') ?>', fd);
    const rzp = new Razorpay({
      key: order.key, amount: order.amount, currency: order.currency, order_id: order.order_id,
      name: order.org, prefill: { name: order.name, email: order.email, contact: order.contact },
      theme: { color: '#007C9C' },
      handler: async function (resp) {
        try {
          await sdPost('<?= site_url('donate/verify') ?>', resp);
          msg.innerHTML = '<div class="alert alert-success">Thank you! Your donation is confirmed and a receipt is on its way to your email.</div>';
          form.reset();
        } catch (err) { msg.innerHTML = '<div class="alert alert-danger">' + err.message + '</div>'; }
      },
      modal: { ondismiss: function () { btn.disabled = false; btn.innerHTML = '<i class="bi bi-heart-fill"></i> Donate now'; } },
    });
    rzp.on('payment.failed', function (r) { msg.innerHTML = '<div class="alert alert-danger">Payment failed: ' + r.error.description + '</div>'; });
    rzp.open();
  } catch (err) {
    msg.innerHTML = '<div class="alert alert-danger">' + err.message + '</div>';
  } finally {
    btn.disabled = false; btn.innerHTML = '<i class="bi bi-heart-fill"></i> Donate now';
  }
});
</script>
</body>
</html>
