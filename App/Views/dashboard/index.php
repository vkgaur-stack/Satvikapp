<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div id="sd-dashboard" data-endpoint="<?= site_url('dashboard/data') ?>"></div>
<noscript>Enable JavaScript to see the dashboard.</noscript>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script crossorigin src="https://cdn.jsdelivr.net/npm/react@18/umd/react.production.min.js"></script>
<script crossorigin src="https://cdn.jsdelivr.net/npm/react-dom@18/umd/react-dom.production.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@babel/standalone@7/babel.min.js"></script>
<script type="text/babel" data-presets="react">
const { useEffect, useState } = React;

function Stat({ label, value, hint, accent }) {
  return (
    <div className="col-6 col-lg-3">
      <div className={"card sd-stat" + (accent ? " accent" : "")}>
        <div className="label">{label}</div>
        <div className="value">{value}</div>
        {hint ? <div className="hint">{hint}</div> : null}
      </div>
    </div>
  );
}

function MiniBars({ data }) {
  const max = Math.max(1, ...data.map(d => d.amount));
  return (
    <div className="sd-bars">
      {data.map((d, i) => (
        <div className="bar" key={i}>
          <b>{sdMoney(d.amount, 0)}</b>
          <i style={{ height: Math.max(4, Math.round(100 * d.amount / max)) + "%" }}></i>
          <span className="mt-1">{d.month}</span>
        </div>
      ))}
    </div>
  );
}

function Dashboard() {
  const [data, setData] = useState(null);
  const [err, setErr] = useState(null);
  useEffect(() => {
    fetch(document.getElementById('sd-dashboard').dataset.endpoint, { credentials: 'same-origin' })
      .then(r => { if (!r.ok) throw new Error('Failed to load'); return r.json(); })
      .then(setData).catch(e => setErr(e.message));
  }, []);

  if (err) return <div className="alert alert-danger">{err}</div>;
  if (!data) return <div className="text-body-secondary">Loading dashboard…</div>;

  const f = data.fundraising, im = data.impact, recent = data.recent_donations || [];

  return (
    <div className="d-flex flex-column gap-4">
      <div className="text-body-secondary small">Financial year {data.fy_label}</div>

      {f && (
        <div className="row g-3">
          <Stat label="Raised this year" value={sdMoney(f.raised_fy, 0)} accent />
          <Stat label="Active donors" value={f.active_donors} hint={f.lapsed_donors + " lapsed"} />
          <Stat label="Retention rate" value={f.retention_rate === null ? "—" : f.retention_rate + "%"} />
          <Stat label="Average gift" value={sdMoney(f.average_gift, 0)} hint={f.gifts_fy + " gifts"} />
        </div>
      )}

      {im && (
        <div className="row g-3">
          <Stat label="Beneficiaries" value={im.beneficiaries} />
          <Stat label="Currently enrolled" value={im.enrolled_now} />
          <Stat label="Active programs" value={im.programs_active} />
          <Stat label="Aid disbursed (FY)" value={sdMoney(im.aid_fy, 0)} hint={im.open_grievances + " open grievances"} />
        </div>
      )}

      <div className="row g-3">
        {f && (
          <div className="col-lg-6">
            <div className="card h-100">
              <div className="card-header">Giving, last 6 months</div>
              <div className="card-body"><MiniBars data={f.monthly} /></div>
            </div>
          </div>
        )}
        {f && (
          <div className="col-lg-6">
            <div className="card h-100">
              <div className="card-header">Campaign progress</div>
              <div className="card-body d-flex flex-column gap-3">
                {f.campaigns.map(c => (
                  <div key={c.id}>
                    <div className="d-flex justify-content-between small mb-1">
                      <span>{c.name}</span>
                      <span className="text-body-secondary">{sdMoney(c.raised, 0)} / {sdMoney(c.goal, 0)}</span>
                    </div>
                    <div className="sd-progress"><span style={{ width: Math.min(100, c.percent || 0) + "%" }}></span></div>
                  </div>
                ))}
                {f.campaigns.length === 0 && <div className="text-body-secondary small">No campaigns yet.</div>}
              </div>
            </div>
          </div>
        )}
      </div>

      {recent.length > 0 && (
        <div className="card">
          <div className="card-header">Recent donations</div>
          <div className="table-responsive">
            <table className="table table-hover mb-0 align-middle">
              <thead><tr><th>Donor</th><th>Campaign</th><th>Amount</th><th>Date</th><th>Payment</th><th>Receipt</th></tr></thead>
              <tbody>
                {recent.map(r => (
                  <tr key={r.id}>
                    <td>{r.donor}</td>
                    <td>{r.campaign || "—"}</td>
                    <td>{sdMoney(r.amount, 0)}</td>
                    <td>{r.date}</td>
                    <td><span className={"badge text-bg-" + (r.payment_status === "captured" ? "success" : r.payment_status === "pending" ? "warning" : "danger")}>{r.payment_status}</span></td>
                    <td><span className={"badge text-bg-" + (r.receipt_status === "issued" ? "success" : "warning")}>{r.receipt_status}</span></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('sd-dashboard')).render(<Dashboard />);
</script>
<?= $this->endSection() ?>
