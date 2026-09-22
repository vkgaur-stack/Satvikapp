<?php $title = 'Donor stewardship'; ?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<p class="text-body-secondary">Donors with no captured gift in the last 6 months. Draft a personal message, review it, then send.</p>
<?php if (! $ai): ?><div class="alert alert-info py-2 small">Drafts are template-based. Set <code>ANTHROPIC_API_KEY</code> in <code>.env</code> to generate personalised AI drafts instead.</div><?php endif; ?>

<?php
$donorPayload = array_map(static fn ($d) => [
    'id' => (int) $d['id'], 'name' => $d['name'], 'email' => $d['email'], 'phone' => $d['phone'],
    'tier' => $d['tier'], 'lifetime_value' => (float) $d['lifetime_value'],
    'last_gift_date' => $d['last_gift_date'], 'pref' => $d['communication_preference'],
], $donors);
?>
<div id="sd-stewardship"
     data-donors='<?= esc(json_encode($donorPayload), 'attr') ?>'
     data-whatsapp="<?= $whatsapp ? '1' : '' ?>"
     data-draft="<?= site_url('stewardship/draft') ?>"
     data-send="<?= site_url('stewardship/send') ?>"></div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script crossorigin src="https://cdn.jsdelivr.net/npm/react@18/umd/react.production.min.js"></script>
<script crossorigin src="https://cdn.jsdelivr.net/npm/react-dom@18/umd/react-dom.production.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@babel/standalone@7/babel.min.js"></script>
<script type="text/babel" data-presets="react">
const { useState } = React;

function App() {
  const root = document.getElementById('sd-stewardship');
  const donors = JSON.parse(root.dataset.donors);
  const whatsapp = !!root.dataset.whatsapp;
  const [sel, setSel] = useState(null);
  const [subject, setSubject] = useState('');
  const [body, setBody] = useState('');
  const [channel, setChannel] = useState('email');
  const [busy, setBusy] = useState(false);
  const [msg, setMsg] = useState(null);
  const [sentIds, setSentIds] = useState([]);

  async function pick(d) {
    setSel(d); setMsg(null); setBody(''); setSubject('');
    setChannel(d.pref === 'whatsapp' ? 'whatsapp' : 'email');
    setBusy(true);
    try {
      const r = await sdPost(root.dataset.draft, { donor_id: d.id });
      setSubject(r.subject); setBody(r.body);
    } catch (e) { setMsg({ type: 'danger', text: e.message }); }
    setBusy(false);
  }

  async function send() {
    setBusy(true); setMsg(null);
    try {
      await sdPost(root.dataset.send, { donor_id: sel.id, channel, subject, body });
      setMsg({ type: 'success', text: 'Sent.' });
      setSentIds([...sentIds, sel.id]);
    } catch (e) { setMsg({ type: 'danger', text: e.message }); }
    setBusy(false);
  }

  return (
    <div className="row g-3">
      <div className="col-lg-5">
        <div className="card">
          <div className="card-header">Lapsed donors ({donors.length})</div>
          <div className="list-group list-group-flush" style={{ maxHeight: 560, overflowY: 'auto' }}>
            {donors.map(d => (
              <button key={d.id} onClick={() => pick(d)} className={"list-group-item list-group-item-action d-flex justify-content-between align-items-center" + (sel && sel.id === d.id ? " active" : "")}>
                <span>
                  {d.name}
                  {sentIds.includes(d.id) && <i className="bi bi-check-circle-fill text-success ms-2" title="Sent"></i>}
                  <br /><small className="text-body-secondary">{d.tier} · last gift {d.last_gift_date || "never"}</small>
                </span>
                <span className="badge text-bg-secondary">{sdMoney(d.lifetime_value, 0)}</span>
              </button>
            ))}
            {donors.length === 0 && <div className="p-3 text-body-secondary">No lapsed donors right now — nice work.</div>}
          </div>
        </div>
      </div>
      <div className="col-lg-7">
        {!sel && <div className="card"><div className="card-body text-body-secondary">Choose a donor to draft a message.</div></div>}
        {sel && (
          <div className="card">
            <div className="card-header d-flex justify-content-between align-items-center">
              <span>Message to {sel.name}</span>
              <div className="btn-group btn-group-sm">
                <button className={"btn btn-outline-secondary" + (channel === "email" ? " active" : "")} onClick={() => setChannel("email")} disabled={!sel.email}>Email</button>
                <button className={"btn btn-outline-secondary" + (channel === "whatsapp" ? " active" : "")} onClick={() => setChannel("whatsapp")} disabled={!whatsapp || !sel.phone}>WhatsApp</button>
              </div>
            </div>
            <div className="card-body">
              {msg && <div className={"alert alert-" + msg.type + " py-2"}>{msg.text}</div>}
              {sel.pref === 'none' && <div className="alert alert-warning py-2">This donor asked not to be contacted.</div>}
              {channel === 'email' && (
                <div className="mb-2"><label className="form-label small">Subject</label><input className="form-control" value={subject} onChange={e => setSubject(e.target.value)} /></div>
              )}
              <label className="form-label small">Message</label>
              <textarea className="form-control" rows={10} value={body} onChange={e => setBody(e.target.value)} disabled={busy}></textarea>
              <div className="d-flex justify-content-between mt-3">
                <button className="btn btn-outline-secondary btn-sm" onClick={() => pick(sel)} disabled={busy}><i className="bi bi-arrow-repeat"></i> Redraft</button>
                <button className="btn btn-accent btn-sm" onClick={send} disabled={busy || !body || sel.pref === 'none'}>{busy ? 'Working…' : 'Send'}</button>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
ReactDOM.createRoot(document.getElementById('sd-stewardship')).render(<App />);
</script>
<?= $this->endSection() ?>
