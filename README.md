# Satvikdaan — Donor & Beneficiary Management System

A CodeIgniter 4 (PHP) web application for a nonprofit running healthcare,
education, vocational-training and food programs. One system covers donor
CRM + online giving (Razorpay) + tax receipts, and beneficiary
registration + program eligibility + aid tracking + case notes, with
RBAC and field-level PII encryption, a REST API, and five built-in
report dashboards.

This is the MVP build (see `/database` and `app/Config/Catalog.php` — the
whole data model and every screen are generated from that one catalog file).
Offline-first field collection is designed for in the schema but not yet
built into the UI; see "What's not built yet" below.

---

## 1. Folder structure

```
satvikdaan/
├── .env                          # your real config (copy from env.example, NEVER commit)
├── env.example                   # documented template — copy to .env
├── composer.json                 # PHP dependencies (CodeIgniter 4, Razorpay SDK)
├── spark                         # CLI entry point (php spark ...)
├── preload.php
├── phpunit.dist.xml
│
├── app/
│   ├── Common.php                 # global helpers: money(), fmt_date(), badge(), org(), can(), amount_in_words()
│   ├── Config/
│   │   ├── App.php, Database.php, Email.php, Encryption.php, Session.php   # stock CI4 config (reads .env)
│   │   ├── Filters.php            # registers 'auth' (web) and 'apiauth' (REST) filters
│   │   ├── Routes.php             # every route in the app
│   │   └── Catalog.php            # ★ THE MODULE CATALOG — add/edit a field or a whole module here
│   ├── Controllers/
│   │   ├── Auth.php               # login / logout
│   │   ├── Account.php            # change password
│   │   ├── Dashboard.php          # / and /dashboard/data (JSON for the React widgets)
│   │   ├── Crud.php               # generic list/show/new/edit/create/update/delete for EVERY module
│   │   ├── Files.php              # encrypted file download + "mark document verified"
│   │   ├── Receipts.php           # tax receipt view / issue / email
│   │   ├── Payments.php           # public Razorpay donate flow + webhook
│   │   ├── Stewardship.php        # lapsed-donor AI/template drafts, send email or WhatsApp
│   │   ├── Reports.php            # the 5 dashboards + CSV export
│   │   ├── Audit.php              # audit log viewer
│   │   └── Api/                   # REST API v1 (Auth, Dashboard, generic Resource controller)
│   ├── Models/                    # one model per table, all soft-delete + timestamped
│   │   ├── BaseModel.php
│   │   ├── Traits/EncryptsFields.php   # AES-256 field encryption, mixed into Beneficiary + CaseNote models
│   │   └── ...DonorModel, DonationModel, BeneficiaryModel, EnrollmentModel, AidDeliveryModel, etc.
│   ├── Libraries/
│   │   ├── ModuleService.php      # ★ the engine: reads Catalog.php, drives every CRUD screen + the API
│   │   ├── Crypto.php             # AES-256 encrypt/decrypt + keyed hash (for duplicate lookups on encrypted cols)
│   │   ├── SecureFiles.php        # encrypted file storage outside the web root
│   │   ├── Eligibility.php        # rule-based program eligibility screening
│   │   ├── Rbac.php, CurrentUser.php, Audit.php   # roles/permissions, request-scoped user, audit trail
│   │   ├── Metrics.php, Reports.php               # dashboard + 5 report queries
│   │   ├── ReceiptService.php     # receipt numbering, issue, email
│   │   └── Mailer.php, WhatsApp.php, DraftWriter.php   # SMTP email, WhatsApp Cloud API, AI/template drafts
│   ├── Filters/AuthFilter.php, ApiAuthFilter.php
│   ├── Commands/RefreshDonorStatus.php   # `php spark donors:refresh` — cron job
│   └── Views/
│       ├── layouts/main.php       # sidebar + topbar shell (Bootstrap 5, dark/light theme)
│       ├── crud/{index,show,form}.php     # generic screens for every module (list/detail/create-edit)
│       ├── dashboard/index.php    # React widgets (CDN React, no build step)
│       ├── stewardship/index.php  # React donor-outreach workspace
│       ├── receipts/{show,email}.php
│       ├── reports/show.php
│       ├── donate/index.php       # public Razorpay Checkout page
│       └── auth/login.php, account/password.php, audit/index.php, errors/forbidden.php
│
├── public/                        # web root — point your vhost here
│   ├── index.php
│   └── assets/{css/app.css, js/app.js}
│
├── database/
│   ├── satvikdaan.sql             # full schema + RBAC seed + default admin login (safe to re-run)
│   └── demo_seed.sql              # optional demo data for a client walkthrough (do NOT load in production)
│
└── writable/                      # logs, cache, sessions, uploads/ (encrypted files) — must be writable by PHP
```

### The pattern that generates every screen

Adding a new field, or an entire new module, means editing **one file**:
`app/Config/Catalog.php`. Each module entry declares its model, its fields
(type, validation rules, which screens show it, PII/identity masking), and
`app/Libraries/ModuleService.php` + `app/Controllers/Crud.php` turn that into
list/search/filter/detail/create/edit/delete screens and REST endpoints
automatically — so Donors, Donations, Beneficiaries, Programs, Enrollments,
Aid Deliveries, Case Notes, Documents and Surveys are all the *same* code
path, not nine separate CRUD implementations.

---

## 2. Requirements

- PHP 8.2 or 8.3, with extensions: `mbstring intl curl json mysqli openssl`
- MySQL 5.7+ or MariaDB 10.4+
- Composer 2
- A Linux host with FTP/SFTP or shell access (no Node/build pipeline needed —
  React is loaded from a CDN with Babel-in-browser, so `public/` is deployable as-is)

## 3. Local / server setup

```bash
composer install --no-dev --optimize-autoloader

cp env.example .env
php spark key:generate            # writes encryption.key into .env — BACK THIS KEY UP SEPARATELY

# edit .env: database.*, email.*, RAZORPAY_*, ORG_*  (see comments in env.example)

mysql -u root -p < database/satvikdaan.sql              # schema + roles/permissions + default admin
mysql -u root -p satvikdaan < database/demo_seed.sql    # OPTIONAL — demo data for a walkthrough

mkdir -p writable/uploads && chmod -R 775 writable
```

Point your web server's document root at `public/`. For quick local testing
without a vhost: `php spark serve` (http://localhost:8080).

**Default admin login** (created by `satvikdaan.sql`): `admin@satvikdaan.org`
/ `Admin@12345` — **change this password on first login** (Account → Change
password). Demo-seed logins, if you loaded `demo_seed.sql`, all use
`Demo@12345`: `pm@`, `field@`, `auditor@`, `donor@satvikdaan.org`.

### Razorpay

1. Get keys from Dashboard → Settings → API Keys; set `RAZORPAY_KEY_ID` /
   `RAZORPAY_KEY_SECRET` in `.env`. `RAZORPAY_KEY_SECRET` is read only on the
   server (`app/Controllers/Payments.php`) — it is never sent to the browser.
2. Dashboard → Settings → Webhooks → add `https://yourdomain/razorpay/webhook`,
   events `payment.captured` and `payment.failed`; put the webhook secret in
   `RAZORPAY_WEBHOOK_SECRET`.
3. Public giving page: `/donate`.

### Email / WhatsApp / AI drafts (all optional, degrade gracefully)

- SMTP settings (`email.*` in `.env`) power receipts and stewardship email.
- WhatsApp needs a Meta WhatsApp Business Cloud API token (`WHATSAPP_TOKEN`,
  `WHATSAPP_PHONE_ID`) — leave blank to hide the WhatsApp option.
- `ANTHROPIC_API_KEY` (optional) turns on AI-drafted lapsed-donor emails in
  Donor Stewardship; without it, a template writer is used instead. Only
  first name, gift dates/amounts and campaign name are ever sent to the API.

### Cron

```
php /path/to/satvikdaan/spark donors:refresh
```
Run daily — recalculates every donor's tier (Bronze/Silver/Gold/Major) and
Active/Lapsed status from captured donations.

---

## 4. Roles (RBAC)

`admin`, `program_manager`, `field_worker`, `donor`, `auditor` — see the
matrix and the full permission list generated in `database/satvikdaan.sql`
(`roles`, `permissions`, `role_permissions` tables). Field workers only ever
see records they created; donors and auditors get masked/aggregate views
(no names, no contact details) via the `identity.view` / `pii.decrypt`
permissions.

## 5. Security notes

- Beneficiary phone, email, national ID and address, and case-note content,
  are AES-256 encrypted at the field level (`app/Libraries/Crypto.php`,
  `app/Models/Traits/EncryptsFields.php`). Duplicate detection uses a keyed
  hash of the value, never the plaintext.
- Uploaded documents / proof-of-delivery photos are encrypted and stored
  under `writable/uploads/`, outside the web root, and served only through
  an authenticated, permission-checked, audited controller (`Files::download`).
- Every view/create/update/delete of PII, every login, every file download
  and every report export is written to `audit_logs` (`app/Libraries/Audit.php`).
- `RAZORPAY_KEY_SECRET` and `RAZORPAY_WEBHOOK_SECRET` are read only in
  `Payments.php`, server-side.
- CSRF protection is on for every web form; the REST API uses bearer tokens
  instead (no cookies, so CSRF doesn't apply) — see `POST /api/v1/auth/token`.

## 6. REST API

`POST /api/v1/auth/token` (email + password) returns a 30-day bearer token.
Then `Authorization: Bearer <token>` on:

```
GET    /api/v1/me
GET    /api/v1/dashboard
GET    /api/v1/{module}              donors | donations | campaigns | beneficiaries |
GET    /api/v1/{module}/{id}         households | programs | enrollments | aid_deliveries |
POST   /api/v1/{module}              case_notes | documents | surveys
PUT    /api/v1/{module}/{id}
DELETE /api/v1/{module}/{id}
```
Every module in `Catalog.php` with `'api' => true` is exposed this way —
same validation, same RBAC, same PII masking as the web screens.

## 7. What's not built yet (see the PRD)

- Offline-first field capture (local queue + sync + server-side dedupe
  reconciliation UI) — the schema (`dedup_flag`, `dedup_of`) and the
  duplicate-flagging logic already run on every create; the offline
  storage/sync layer is the next phase.
- Attendance tracking UI (table exists: `attendance`).
- Pledges UI (table exists: `pledges`).

Report export now supports **CSV, PDF and Excel (.xlsx)** — see the Export
dropdown on any report page, or `GET /reports/{slug}/export?format=csv|pdf|xlsx`.
PDF rendering uses `dompdf/dompdf`; Excel uses `phpoffice/phpspreadsheet`
(both added to `composer.json` — run `composer install` to pull them in).
Money values in the PDF print as "Rs. 1,23,456.00" rather than the ₹ symbol,
because dompdf's bundled fonts don't include the ₹ glyph; the web UI and the
Excel export both keep ₹ since browsers and Excel render it correctly.

---

Questions, or want the next phase scoped out (offline sync, attendance,
pledges, or the later migration to Next.js/TypeScript)? Just ask.
