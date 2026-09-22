-- =====================================================================
--  Satvikdaan - Donor & Beneficiary Management System
--  Database creation script  (MySQL 5.7+ / MariaDB 10.4+, InnoDB, utf8mb4)
--  Safe to re-run: uses IF NOT EXISTS / INSERT IGNORE (never drops data).
--  Run:  mysql -u root -p < database/satvikdaan.sql
-- =====================================================================
CREATE DATABASE IF NOT EXISTS satvikdaan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE satvikdaan;
SET NAMES utf8mb4;

-- ---------- Access control -------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(40)  NOT NULL,
  label       VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS permissions (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug        VARCHAR(60)  NOT NULL,
  label       VARCHAR(150) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_permissions_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id       INT UNSIGNED NOT NULL,
  permission_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_id       INT UNSIGNED NOT NULL,
  name          VARCHAR(120) NOT NULL,
  email         VARCHAR(150) NOT NULL,
  phone         VARCHAR(20)  NULL,
  password_hash VARCHAR(255) NOT NULL,
  status        ENUM('active','disabled') NOT NULL DEFAULT 'active',
  last_login_at DATETIME NULL,
  created_at    DATETIME NULL,
  updated_at    DATETIME NULL,
  deleted_at    DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  KEY ix_users_role (role_id),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS api_tokens (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id      BIGINT UNSIGNED NOT NULL,
  token_hash   CHAR(64) NOT NULL,
  name         VARCHAR(80) NULL,
  last_used_at DATETIME NULL,
  expires_at   DATETIME NOT NULL,
  created_at   DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_api_tokens_hash (token_hash),
  CONSTRAINT fk_tokens_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Fundraising ----------------------------------------------
CREATE TABLE IF NOT EXISTS campaigns (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(150) NOT NULL,
  category    ENUM('general','healthcare','education','vocational','food','disaster_relief') NOT NULL DEFAULT 'general',
  target_goal DECIMAL(14,2) NOT NULL DEFAULT 0,
  start_date  DATE NULL,
  end_date    DATE NULL,
  description TEXT NULL,
  created_at  DATETIME NULL,
  updated_at  DATETIME NULL,
  deleted_at  DATETIME NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS donors (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(150) NOT NULL,
  email         VARCHAR(150) NULL,
  phone         VARCHAR(20)  NULL,
  address       VARCHAR(255) NULL,
  city          VARCHAR(80)  NULL,
  state         VARCHAR(80)  NULL,
  pan           VARCHAR(10)  NULL COMMENT 'Needed on 80G receipts for donations above the statutory limit',
  tier          ENUM('bronze','silver','gold','major') NOT NULL DEFAULT 'bronze',
  status        ENUM('active','lapsed','inactive') NOT NULL DEFAULT 'active',
  communication_preference ENUM('email','whatsapp','both','none') NOT NULL DEFAULT 'email',
  lifetime_value DECIMAL(14,2) NOT NULL DEFAULT 0,
  last_gift_date DATE NULL,
  notes         TEXT NULL,
  created_by    BIGINT UNSIGNED NULL,
  created_at    DATETIME NULL,
  updated_at    DATETIME NULL,
  deleted_at    DATETIME NULL,
  PRIMARY KEY (id),
  KEY ix_donors_email (email),
  KEY ix_donors_status_tier (status, tier),
  KEY ix_donors_last_gift (last_gift_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS donations (
  id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  donor_id            BIGINT UNSIGNED NOT NULL,
  campaign_id         BIGINT UNSIGNED NULL,
  amount              DECIMAL(12,2) NOT NULL,
  currency            CHAR(3) NOT NULL DEFAULT 'INR',
  donated_on          DATE NOT NULL,
  payment_method      ENUM('online','cheque','cash','bank_transfer','in_kind') NOT NULL DEFAULT 'online',
  payment_status      ENUM('pending','captured','failed') NOT NULL DEFAULT 'captured',
  frequency           ENUM('one_off','monthly','annual') NOT NULL DEFAULT 'one_off',
  receipt_status      ENUM('pending','issued') NOT NULL DEFAULT 'pending',
  receipt_no          VARCHAR(30) NULL,
  receipt_issued_at   DATETIME NULL,
  reference_no        VARCHAR(80) NULL COMMENT 'Cheque no. / UTR / in-kind reference',
  razorpay_order_id   VARCHAR(40) NULL,
  razorpay_payment_id VARCHAR(40) NULL,
  notes               VARCHAR(500) NULL,
  created_by          BIGINT UNSIGNED NULL,
  created_at          DATETIME NULL,
  updated_at          DATETIME NULL,
  deleted_at          DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_donations_receipt (receipt_no),
  UNIQUE KEY uq_donations_rzp_payment (razorpay_payment_id),
  KEY ix_donations_rzp_order (razorpay_order_id),
  KEY ix_donations_donor (donor_id),
  KEY ix_donations_campaign (campaign_id),
  KEY ix_donations_date (donated_on),
  CONSTRAINT fk_donations_donor    FOREIGN KEY (donor_id)    REFERENCES donors (id),
  CONSTRAINT fk_donations_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns (id)
) ENGINE=InnoDB;

-- Schema-ready for phase 2 (no screens yet)
CREATE TABLE IF NOT EXISTS pledges (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  donor_id    BIGINT UNSIGNED NOT NULL,
  campaign_id BIGINT UNSIGNED NULL,
  amount      DECIMAL(12,2) NOT NULL,
  frequency   ENUM('one_off','monthly','annual') NOT NULL DEFAULT 'one_off',
  start_date  DATE NOT NULL,
  end_date    DATE NULL,
  status      ENUM('open','fulfilled','cancelled') NOT NULL DEFAULT 'open',
  created_at  DATETIME NULL,
  updated_at  DATETIME NULL,
  deleted_at  DATETIME NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_pledges_donor FOREIGN KEY (donor_id) REFERENCES donors (id),
  CONSTRAINT fk_pledges_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS communications (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  donor_id   BIGINT UNSIGNED NOT NULL,
  channel    ENUM('email','whatsapp') NOT NULL,
  subject    VARCHAR(200) NULL,
  body       TEXT NULL,
  status     ENUM('sent','failed') NOT NULL,
  error      VARCHAR(500) NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY ix_comm_donor (donor_id),
  CONSTRAINT fk_comm_donor FOREIGN KEY (donor_id) REFERENCES donors (id)
) ENGINE=InnoDB;

-- ---------- Programs & beneficiaries ---------------------------------
CREATE TABLE IF NOT EXISTS programs (
  id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name                VARCHAR(150) NOT NULL,
  category            ENUM('healthcare','education','vocational','food') NOT NULL,
  description         TEXT NULL,
  target_beneficiaries INT UNSIGNED NOT NULL DEFAULT 0,
  budget              DECIMAL(14,2) NOT NULL DEFAULT 0,
  start_date          DATE NULL,
  end_date            DATE NULL,
  status              ENUM('active','paused','completed') NOT NULL DEFAULT 'active',
  min_age             TINYINT UNSIGNED NULL,
  max_age             TINYINT UNSIGNED NULL,
  max_monthly_income  DECIMAL(12,2) NULL,
  gender_target       ENUM('any','female','male') NOT NULL DEFAULT 'any',
  created_at          DATETIME NULL,
  updated_at          DATETIME NULL,
  deleted_at          DATETIME NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS beneficiaries (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name       VARCHAR(150) NOT NULL,
  gender          ENUM('female','male','other') NOT NULL,
  dob             DATE NULL,
  phone           TEXT NULL COMMENT 'AES-256 encrypted (base64)',
  phone_hash      CHAR(64) NULL COMMENT 'HMAC-SHA256 for duplicate detection',
  email           TEXT NULL COMMENT 'AES-256 encrypted',
  id_type         ENUM('aadhaar','voter_id','pan','driving_license','ration_card','none') NOT NULL DEFAULT 'none',
  id_number       TEXT NULL COMMENT 'AES-256 encrypted',
  id_number_hash  CHAR(64) NULL COMMENT 'HMAC-SHA256 for duplicate detection',
  no_id_reason    VARCHAR(255) NULL,
  address         TEXT NULL COMMENT 'AES-256 encrypted',
  village         VARCHAR(100) NULL,
  district        VARCHAR(100) NULL,
  state           VARCHAR(80)  NULL,
  pincode         CHAR(6) NULL,
  category        ENUM('rural_woman','marginalized_youth','disaster_affected','underprivileged_family') NOT NULL,
  status          ENUM('registered','verified','active','graduated','inactive') NOT NULL DEFAULT 'registered',
  consent_given   TINYINT(1) NOT NULL DEFAULT 0,
  consent_date    DATE NULL,
  dedup_flag      TINYINT(1) NOT NULL DEFAULT 0,
  dedup_of        BIGINT UNSIGNED NULL,
  created_by      BIGINT UNSIGNED NULL,
  created_at      DATETIME NULL,
  updated_at      DATETIME NULL,
  deleted_at      DATETIME NULL,
  PRIMARY KEY (id),
  KEY ix_ben_idhash (id_number_hash),
  KEY ix_ben_phonehash (phone_hash),
  KEY ix_ben_name_dob (full_name, dob),
  KEY ix_ben_status (status),
  KEY ix_ben_district (district),
  KEY ix_ben_created_by (created_by)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS households (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  beneficiary_id BIGINT UNSIGNED NOT NULL COMMENT 'Head of household',
  members_count  TINYINT UNSIGNED NOT NULL DEFAULT 1,
  monthly_income DECIMAL(12,2) NULL,
  latitude       DECIMAL(9,6) NULL,
  longitude      DECIMAL(9,6) NULL,
  notes          VARCHAR(500) NULL,
  created_by     BIGINT UNSIGNED NULL,
  created_at     DATETIME NULL,
  updated_at     DATETIME NULL,
  deleted_at     DATETIME NULL,
  PRIMARY KEY (id),
  KEY ix_hh_ben (beneficiary_id),
  CONSTRAINT fk_hh_ben FOREIGN KEY (beneficiary_id) REFERENCES beneficiaries (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS enrollments (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  beneficiary_id    BIGINT UNSIGNED NOT NULL,
  program_id        BIGINT UNSIGNED NOT NULL,
  status            ENUM('pending','eligible','ineligible','enrolled','completed','dropped') NOT NULL DEFAULT 'pending',
  eligibility_score TINYINT UNSIGNED NULL,
  eligibility_notes VARCHAR(500) NULL,
  enrolled_on       DATE NULL,
  completed_on      DATE NULL,
  verified_by       BIGINT UNSIGNED NULL,
  created_by        BIGINT UNSIGNED NULL,
  created_at        DATETIME NULL,
  updated_at        DATETIME NULL,
  deleted_at        DATETIME NULL,
  PRIMARY KEY (id),
  KEY ix_enr_ben (beneficiary_id),
  KEY ix_enr_prog_status (program_id, status),
  CONSTRAINT fk_enr_ben  FOREIGN KEY (beneficiary_id) REFERENCES beneficiaries (id),
  CONSTRAINT fk_enr_prog FOREIGN KEY (program_id)     REFERENCES programs (id)
) ENGINE=InnoDB;

-- Schema-ready for phase 2 (multi-session attendance screens)
CREATE TABLE IF NOT EXISTS attendance (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  enrollment_id BIGINT UNSIGNED NOT NULL,
  session_date  DATE NOT NULL,
  present       TINYINT(1) NOT NULL DEFAULT 1,
  notes         VARCHAR(255) NULL,
  recorded_by   BIGINT UNSIGNED NULL,
  created_at    DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_att (enrollment_id, session_date),
  CONSTRAINT fk_att_enr FOREIGN KEY (enrollment_id) REFERENCES enrollments (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS aid_deliveries (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  beneficiary_id BIGINT UNSIGNED NOT NULL,
  program_id     BIGINT UNSIGNED NOT NULL,
  enrollment_id  BIGINT UNSIGNED NULL,
  delivery_type  ENUM('cash','in_kind','service') NOT NULL,
  amount         DECIMAL(12,2) NULL COMMENT 'Cash amount or estimated value of in-kind aid',
  description    VARCHAR(500) NULL,
  delivered_on   DATE NOT NULL,
  status         ENUM('pending','delivered','verified') NOT NULL DEFAULT 'pending',
  proof_file     VARCHAR(255) NULL COMMENT 'Encrypted photo/signature stored outside web root',
  proof_name     VARCHAR(255) NULL,
  verified_by    BIGINT UNSIGNED NULL,
  created_by     BIGINT UNSIGNED NULL,
  created_at     DATETIME NULL,
  updated_at     DATETIME NULL,
  deleted_at     DATETIME NULL,
  PRIMARY KEY (id),
  KEY ix_aid_ben (beneficiary_id),
  KEY ix_aid_prog (program_id, status),
  KEY ix_aid_date (delivered_on),
  CONSTRAINT fk_aid_ben  FOREIGN KEY (beneficiary_id) REFERENCES beneficiaries (id),
  CONSTRAINT fk_aid_prog FOREIGN KEY (program_id)     REFERENCES programs (id),
  CONSTRAINT fk_aid_enr  FOREIGN KEY (enrollment_id)  REFERENCES enrollments (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS case_notes (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  beneficiary_id BIGINT UNSIGNED NOT NULL,
  note_type      ENUM('progress','referral','grievance','counseling') NOT NULL DEFAULT 'progress',
  title          VARCHAR(200) NOT NULL,
  content        TEXT NOT NULL COMMENT 'AES-256 encrypted',
  action_items   VARCHAR(1000) NULL,
  status         ENUM('open','resolved') NOT NULL DEFAULT 'open',
  resolved_at    DATETIME NULL,
  created_by     BIGINT UNSIGNED NULL,
  created_at     DATETIME NULL,
  updated_at     DATETIME NULL,
  deleted_at     DATETIME NULL,
  PRIMARY KEY (id),
  KEY ix_case_ben (beneficiary_id),
  KEY ix_case_type_status (note_type, status),
  CONSTRAINT fk_case_ben FOREIGN KEY (beneficiary_id) REFERENCES beneficiaries (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS documents (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  beneficiary_id BIGINT UNSIGNED NOT NULL,
  document_type  ENUM('id','medical','education','consent','other') NOT NULL,
  title          VARCHAR(200) NOT NULL,
  file_path      VARCHAR(255) NULL COMMENT 'Encrypted file, stored under writable/uploads (outside web root)',
  original_name  VARCHAR(255) NULL,
  mime_type      VARCHAR(100) NULL,
  file_size      INT UNSIGNED NULL,
  verified_by    BIGINT UNSIGNED NULL,
  verified_at    DATETIME NULL,
  created_by     BIGINT UNSIGNED NULL,
  created_at     DATETIME NULL,
  updated_at     DATETIME NULL,
  deleted_at     DATETIME NULL,
  PRIMARY KEY (id),
  KEY ix_doc_ben (beneficiary_id),
  CONSTRAINT fk_doc_ben FOREIGN KEY (beneficiary_id) REFERENCES beneficiaries (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS surveys (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  beneficiary_id BIGINT UNSIGNED NOT NULL,
  program_id     BIGINT UNSIGNED NULL,
  survey_type    ENUM('baseline','endline','post_distribution') NOT NULL,
  monthly_income DECIMAL(12,2) NULL,
  outcome_score  TINYINT UNSIGNED NULL COMMENT '0-100',
  notes          VARCHAR(500) NULL,
  surveyed_on    DATE NOT NULL,
  created_by     BIGINT UNSIGNED NULL,
  created_at     DATETIME NULL,
  updated_at     DATETIME NULL,
  deleted_at     DATETIME NULL,
  PRIMARY KEY (id),
  KEY ix_survey_ben (beneficiary_id, survey_type),
  CONSTRAINT fk_survey_ben  FOREIGN KEY (beneficiary_id) REFERENCES beneficiaries (id),
  CONSTRAINT fk_survey_prog FOREIGN KEY (program_id)     REFERENCES programs (id)
) ENGINE=InnoDB;

-- ---------- Audit -----------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_logs (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id       BIGINT UNSIGNED NULL,
  action        VARCHAR(30)  NOT NULL,
  resource_type VARCHAR(50)  NULL,
  resource_id   BIGINT UNSIGNED NULL,
  ip_address    VARCHAR(45)  NULL,
  user_agent    VARCHAR(255) NULL,
  before_json   LONGTEXT NULL,
  after_json    LONGTEXT NULL,
  created_at    DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY ix_audit_user (user_id, created_at),
  KEY ix_audit_resource (resource_type, resource_id)
) ENGINE=InnoDB;


-- ---------------------------------------------------------------
-- SEED: roles, permissions, role-permission matrix
-- ---------------------------------------------------------------
INSERT IGNORE INTO roles (id, name, label) VALUES
  (1, 'admin', 'Administrator'),
  (2, 'program_manager', 'Program Manager / Case Worker'),
  (3, 'field_worker', 'Field Worker / Data Collector'),
  (4, 'donor', 'Donor (aggregate view)'),
  (5, 'auditor', 'External Auditor (masked)');
INSERT IGNORE INTO permissions (slug, label) VALUES
  ('donors.view', 'View donors'),
  ('donors.create', 'Create donors'),
  ('donors.update', 'Update donors'),
  ('donors.delete', 'Delete donors'),
  ('donations.view', 'View donations'),
  ('donations.create', 'Create donations'),
  ('donations.update', 'Update donations'),
  ('donations.delete', 'Delete donations'),
  ('campaigns.view', 'View campaigns'),
  ('campaigns.create', 'Create campaigns'),
  ('campaigns.update', 'Update campaigns'),
  ('campaigns.delete', 'Delete campaigns'),
  ('beneficiaries.view', 'View beneficiaries'),
  ('beneficiaries.create', 'Create beneficiaries'),
  ('beneficiaries.update', 'Update beneficiaries'),
  ('beneficiaries.delete', 'Delete beneficiaries'),
  ('households.view', 'View households'),
  ('households.create', 'Create households'),
  ('households.update', 'Update households'),
  ('households.delete', 'Delete households'),
  ('programs.view', 'View programs'),
  ('programs.create', 'Create programs'),
  ('programs.update', 'Update programs'),
  ('programs.delete', 'Delete programs'),
  ('enrollments.view', 'View enrollments'),
  ('enrollments.create', 'Create enrollments'),
  ('enrollments.update', 'Update enrollments'),
  ('enrollments.delete', 'Delete enrollments'),
  ('aid_deliveries.view', 'View aid deliveries'),
  ('aid_deliveries.create', 'Create aid deliveries'),
  ('aid_deliveries.update', 'Update aid deliveries'),
  ('aid_deliveries.delete', 'Delete aid deliveries'),
  ('case_notes.view', 'View case notes'),
  ('case_notes.create', 'Create case notes'),
  ('case_notes.update', 'Update case notes'),
  ('case_notes.delete', 'Delete case notes'),
  ('documents.view', 'View documents'),
  ('documents.create', 'Create documents'),
  ('documents.update', 'Update documents'),
  ('documents.delete', 'Delete documents'),
  ('surveys.view', 'View surveys'),
  ('surveys.create', 'Create surveys'),
  ('surveys.update', 'Update surveys'),
  ('surveys.delete', 'Delete surveys'),
  ('users.view', 'View users'),
  ('users.create', 'Create users'),
  ('users.update', 'Update users'),
  ('users.delete', 'Delete users'),
  ('dashboard.view', 'View dashboard'),
  ('dashboard.fundraising', 'Dashboard: fundraising figures'),
  ('dashboard.impact', 'Dashboard: program impact figures'),
  ('reports.view', 'View reports'),
  ('reports.export', 'Export reports (CSV)'),
  ('audit.view', 'View audit log'),
  ('stewardship.manage', 'Donor stewardship (drafts, email, WhatsApp)'),
  ('receipts.issue', 'Issue and email tax receipts'),
  ('pii.decrypt', 'See decrypted PII (phone, email, ID, address, case notes)'),
  ('identity.view', 'See real names of donors and beneficiaries');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE name='admin'), id FROM permissions WHERE slug IN ('aid_deliveries.create','aid_deliveries.delete','aid_deliveries.update','aid_deliveries.view','audit.view','beneficiaries.create','beneficiaries.delete','beneficiaries.update','beneficiaries.view','campaigns.create','campaigns.delete','campaigns.update','campaigns.view','case_notes.create','case_notes.delete','case_notes.update','case_notes.view','dashboard.fundraising','dashboard.impact','dashboard.view','documents.create','documents.delete','documents.update','documents.view','donations.create','donations.delete','donations.update','donations.view','donors.create','donors.delete','donors.update','donors.view','enrollments.create','enrollments.delete','enrollments.update','enrollments.view','households.create','households.delete','households.update','households.view','identity.view','pii.decrypt','programs.create','programs.delete','programs.update','programs.view','receipts.issue','reports.export','reports.view','stewardship.manage','surveys.create','surveys.delete','surveys.update','surveys.view','users.create','users.delete','users.update','users.view');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE name='program_manager'), id FROM permissions WHERE slug IN ('aid_deliveries.create','aid_deliveries.update','aid_deliveries.view','beneficiaries.create','beneficiaries.update','beneficiaries.view','campaigns.view','case_notes.create','case_notes.update','case_notes.view','dashboard.fundraising','dashboard.impact','dashboard.view','documents.create','documents.update','documents.view','donations.view','donors.view','enrollments.create','enrollments.update','enrollments.view','households.create','households.update','households.view','identity.view','pii.decrypt','programs.create','programs.update','programs.view','reports.export','reports.view','surveys.create','surveys.update','surveys.view');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE name='field_worker'), id FROM permissions WHERE slug IN ('aid_deliveries.create','aid_deliveries.view','beneficiaries.create','beneficiaries.update','beneficiaries.view','case_notes.create','case_notes.view','dashboard.impact','dashboard.view','documents.create','documents.view','enrollments.create','enrollments.view','households.create','households.update','households.view','identity.view','programs.view','surveys.create','surveys.view');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE name='donor'), id FROM permissions WHERE slug IN ('campaigns.view','dashboard.fundraising','dashboard.impact','dashboard.view');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE name='auditor'), id FROM permissions WHERE slug IN ('aid_deliveries.view','audit.view','beneficiaries.view','campaigns.view','case_notes.view','dashboard.fundraising','dashboard.impact','dashboard.view','documents.view','donations.view','donors.view','enrollments.view','households.view','programs.view','reports.export','reports.view','surveys.view');

-- Default administrator. LOGIN: admin@satvikdaan.org / Admin@12345  -> CHANGE THE PASSWORD ON FIRST LOGIN
INSERT IGNORE INTO users (role_id, name, email, password_hash, status, created_at, updated_at)
VALUES ((SELECT id FROM roles WHERE name='admin'), 'System Administrator', 'admin@satvikdaan.org', '$2y$10$AGQORQxrLGY06PbZ2DggZOdr5HauElWNkrZRr2KV7zfEsRhP4zMy2', 'active', NOW(), NOW());
