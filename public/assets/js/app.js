// Theme toggle (dark is default) + small helpers shared by every page.
(function () {
  const root = document.documentElement;
  root.setAttribute('data-bs-theme', localStorage.getItem('sd-theme') || 'dark');
  document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('theme-toggle');
    if (btn) {
      const paint = () => { btn.innerHTML = root.getAttribute('data-bs-theme') === 'dark' ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>'; };
      paint();
      btn.addEventListener('click', function () {
        const next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-bs-theme', next);
        localStorage.setItem('sd-theme', next);
        paint();
      });
    }
    document.querySelectorAll('form[data-confirm]').forEach(function (f) {
      f.addEventListener('submit', function (e) { if (!confirm(f.getAttribute('data-confirm'))) e.preventDefault(); });
    });
  });
})();

// fetch() wrapper that sends the CSRF token and JSON.
window.sdPost = async function (url, body) {
  const token = document.querySelector('meta[name="csrf-token"]').content;
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify(body),
    credentials: 'same-origin',
  });
  let data = {};
  try { data = await res.json(); } catch (e) {}
  if (!res.ok) throw new Error(data.error || 'Request failed (' + res.status + ')');
  return data;
};

// Indian number format for React widgets: 12,34,567
window.sdMoney = function (n, decimals) {
  if (n === null || n === undefined) return '—';
  return '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: decimals || 0, maximumFractionDigits: decimals || 0 });
};
