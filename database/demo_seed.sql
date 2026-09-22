-- =====================================================================
--  DEMO DATA ONLY - for the client walkthrough. Do NOT load in production.
--  Loads after satvikdaan.sql:   mysql -u root -p satvikdaan < database/demo_seed.sql
--  Demo logins (password for all: Demo@12345)
--    pm@satvikdaan.org       Program Manager
--    field@satvikdaan.org    Field Worker
--    auditor@satvikdaan.org  External Auditor (masked view)
--    donor@satvikdaan.org    Donor (aggregate view)
--  Beneficiary phone/ID/address are encrypted by the application, so demo
--  beneficiaries are seeded without them - add them through the UI to see encryption.
-- =====================================================================
USE satvikdaan;
SET NAMES utf8mb4;

INSERT IGNORE INTO users (role_id, name, email, phone, password_hash, status, created_at, updated_at) VALUES
 ((SELECT id FROM roles WHERE name='program_manager'), 'Amit Verma',  'pm@satvikdaan.org',      '9800000001', '$2y$10$Ck6uo0l5PB/ZD9oh.Uz3guVglNZAepi7DqwFB6.U4.1K3QI0GrWUy', 'active', NOW(), NOW()),
 ((SELECT id FROM roles WHERE name='field_worker'),    'Rajini Devi', 'field@satvikdaan.org',   '9800000002', '$2y$10$Ck6uo0l5PB/ZD9oh.Uz3guVglNZAepi7DqwFB6.U4.1K3QI0GrWUy', 'active', NOW(), NOW()),
 ((SELECT id FROM roles WHERE name='auditor'),         'Priya Nair',  'auditor@satvikdaan.org', '9800000003', '$2y$10$Ck6uo0l5PB/ZD9oh.Uz3guVglNZAepi7DqwFB6.U4.1K3QI0GrWUy', 'active', NOW(), NOW()),
 ((SELECT id FROM roles WHERE name='donor'),           'Demo Donor',  'donor@satvikdaan.org',   NULL,         '$2y$10$Ck6uo0l5PB/ZD9oh.Uz3guVglNZAepi7DqwFB6.U4.1K3QI0GrWUy', 'active', NOW(), NOW());

INSERT INTO campaigns (id, name, category, target_goal, start_date, end_date, description, created_at, updated_at) VALUES
 (1, 'Winter Warmth 2026',            'general',        500000.00, DATE_SUB(CURDATE(), INTERVAL 90 DAY),  DATE_ADD(CURDATE(), INTERVAL 90 DAY),  'Blankets and warm food for rural families.', NOW(), NOW()),
 (2, 'Girls Skill Academy',           'vocational',     800000.00, DATE_SUB(CURDATE(), INTERVAL 200 DAY), DATE_ADD(CURDATE(), INTERVAL 165 DAY), 'Tailoring and digital-literacy training for rural women.', NOW(), NOW()),
 (3, 'Village Health Camps',          'healthcare',     350000.00, DATE_SUB(CURDATE(), INTERVAL 120 DAY), DATE_ADD(CURDATE(), INTERVAL 60 DAY),  'Free check-ups and medicines in remote villages.', NOW(), NOW()),
 (4, 'Flood Relief Fund',             'disaster_relief',1000000.00,DATE_SUB(CURDATE(), INTERVAL 45 DAY),  DATE_ADD(CURDATE(), INTERVAL 45 DAY),  'Ration kits and shelter for flood-affected families.', NOW(), NOW());

INSERT INTO donors (id, name, email, phone, city, state, pan, communication_preference, notes, created_at, updated_at) VALUES
 (1, 'Ananya Sharma',       'ananya.sharma@example.com', '9810011111', 'Mumbai',    'Maharashtra', 'ABCPS1234K', 'both',     'Monthly giver since 2025. Prefers WhatsApp updates.', NOW(), NOW()),
 (2, 'Rohan Mehta',         'rohan.mehta@example.com',   '9820022222', 'Pune',      'Maharashtra', NULL,         'email',    'Attended the annual gala.', NOW(), NOW()),
 (3, 'Kavita Iyer',         'kavita.iyer@example.com',   '9830033333', 'Chennai',   'Tamil Nadu',  'AAAPI4321L', 'email',    NULL, NOW(), NOW()),
 (4, 'Sunrise Pharma Ltd.', 'csr@sunrisepharma.example', '9840044444', 'Hyderabad', 'Telangana',  'AABCS9876M', 'email',    'CSR partner - healthcare camps.', NOW(), NOW()),
 (5, 'Imran Qureshi',       'imran.q@example.com',       '9850055555', 'Delhi',     'Delhi',       NULL,         'whatsapp', NULL, NOW(), NOW()),
 (6, 'Meera Pillai',        'meera.pillai@example.com',  '9860066666', 'Kochi',     'Kerala',      NULL,         'email',    'Gave to Flood Relief last year.', NOW(), NOW()),
 (7, 'Vikram Singh',        'vikram.singh@example.com',  '9870077777', 'Jaipur',    'Rajasthan',   NULL,         'none',     'Asked not to be contacted.', NOW(), NOW()),
 (8, 'Lakshmi Foundation',  'contact@lakshmifdn.example','9880088888', 'Bengaluru', 'Karnataka',   'AAATL1122P', 'both',     'Family foundation, gives annually.', NOW(), NOW());

INSERT INTO donations (donor_id, campaign_id, amount, donated_on, payment_method, payment_status, frequency, receipt_status, receipt_no, receipt_issued_at, created_at, updated_at) VALUES
 (1, 2,  5000.00, DATE_SUB(CURDATE(), INTERVAL 20 DAY),  'online','captured','monthly','issued','SD/DEMO/000001', NOW(), NOW(), NOW()),
 (1, 2,  5000.00, DATE_SUB(CURDATE(), INTERVAL 50 DAY),  'online','captured','monthly','issued','SD/DEMO/000002', NOW(), NOW(), NOW()),
 (1, 2,  5000.00, DATE_SUB(CURDATE(), INTERVAL 80 DAY),  'online','captured','monthly','issued','SD/DEMO/000003', NOW(), NOW(), NOW()),
 (2, 1, 25000.00, DATE_SUB(CURDATE(), INTERVAL 15 DAY),  'bank_transfer','captured','one_off','pending',NULL,NULL, NOW(), NOW()),
 (2, 3, 15000.00, DATE_SUB(CURDATE(), INTERVAL 260 DAY), 'cheque','captured','one_off','issued','SD/DEMO/000004', NOW(), NOW(), NOW()),
 (3, 1,  2500.00, DATE_SUB(CURDATE(), INTERVAL 230 DAY), 'online','captured','one_off','issued','SD/DEMO/000005', NOW(), NOW(), NOW()),
 (4, 3,250000.00, DATE_SUB(CURDATE(), INTERVAL 40 DAY),  'bank_transfer','captured','one_off','issued','SD/DEMO/000006', NOW(), NOW(), NOW()),
 (4, 3,150000.00, DATE_SUB(CURDATE(), INTERVAL 400 DAY), 'bank_transfer','captured','one_off','issued','SD/DEMO/000007', NOW(), NOW(), NOW()),
 (5, 4, 10000.00, DATE_SUB(CURDATE(), INTERVAL 30 DAY),  'online','captured','one_off','pending',NULL,NULL, NOW(), NOW()),
 (6, 4,  7500.00, DATE_SUB(CURDATE(), INTERVAL 300 DAY), 'online','captured','one_off','issued','SD/DEMO/000008', NOW(), NOW(), NOW()),
 (7, 2,  3000.00, DATE_SUB(CURDATE(), INTERVAL 320 DAY), 'cash','captured','one_off','issued','SD/DEMO/000009', NOW(), NOW(), NOW()),
 (8, 2,100000.00, DATE_SUB(CURDATE(), INTERVAL 10 DAY),  'cheque','captured','annual','pending',NULL,NULL, NOW(), NOW()),
 (8, 2, 75000.00, DATE_SUB(CURDATE(), INTERVAL 375 DAY), 'cheque','captured','annual','issued','SD/DEMO/000010', NOW(), NOW(), NOW()),
 (5, 1,  2000.00, DATE_SUB(CURDATE(), INTERVAL 5 DAY),   'in_kind','captured','one_off','pending',NULL,NULL, NOW(), NOW());

-- Recompute donor totals, last gift, tier and status (the app does this on every save)
UPDATE donors d SET
  lifetime_value = (SELECT COALESCE(SUM(amount),0) FROM donations x WHERE x.donor_id=d.id AND x.payment_status='captured' AND x.deleted_at IS NULL),
  last_gift_date = (SELECT MAX(donated_on)          FROM donations x WHERE x.donor_id=d.id AND x.payment_status='captured' AND x.deleted_at IS NULL);
UPDATE donors SET
  tier   = CASE WHEN lifetime_value >= 200000 THEN 'major' WHEN lifetime_value >= 50000 THEN 'gold' WHEN lifetime_value >= 10000 THEN 'silver' ELSE 'bronze' END,
  status = CASE WHEN last_gift_date IS NULL OR last_gift_date < DATE_SUB(CURDATE(), INTERVAL 6 MONTH) THEN 'lapsed' ELSE 'active' END;

INSERT INTO programs (id, name, category, description, target_beneficiaries, budget, start_date, end_date, status, min_age, max_age, max_monthly_income, gender_target, created_at, updated_at) VALUES
 (1, 'Rural Women Tailoring Course', 'vocational', '6-month tailoring and micro-enterprise training.', 120, 900000.00, DATE_SUB(CURDATE(), INTERVAL 150 DAY), DATE_ADD(CURDATE(), INTERVAL 210 DAY), 'active', 18, 45, 15000.00, 'female', NOW(), NOW()),
 (2, 'Scholarship & Tutoring',       'education',  'School fees support and after-school tutoring.',   200, 1200000.00,DATE_SUB(CURDATE(), INTERVAL 180 DAY), DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'active', 6,  18, 12000.00, 'any',    NOW(), NOW()),
 (3, 'Village Health Camps',         'healthcare', 'Quarterly camps with free check-ups and medicines.',300, 600000.00, DATE_SUB(CURDATE(), INTERVAL 120 DAY), DATE_ADD(CURDATE(), INTERVAL 60 DAY),  'active', NULL, NULL, NULL,    'any',    NOW(), NOW()),
 (4, 'Nutrition Ration Support',     'food',       'Monthly dry-ration kits for vulnerable households.',150, 700000.00, DATE_SUB(CURDATE(), INTERVAL 90 DAY),  DATE_ADD(CURDATE(), INTERVAL 270 DAY), 'active', NULL, NULL, 10000.00, 'any',    NOW(), NOW());

INSERT INTO beneficiaries (id, full_name, gender, dob, id_type, no_id_reason, village, district, state, pincode, category, status, consent_given, consent_date, created_by, created_at, updated_at) VALUES
 (1, 'Sunita Devi',   'female', '1988-03-14', 'none', 'Aadhaar application pending', 'Khera',      'Jaipur',  'Rajasthan',   '303001', 'rural_woman',            'verified',   1, DATE_SUB(CURDATE(), INTERVAL 140 DAY), (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (2, 'Lalita Bai',    'female', '1992-07-02', 'none', NULL,                        'Rampur',     'Jaipur',  'Rajasthan',   '303002', 'rural_woman',            'active',     1, DATE_SUB(CURDATE(), INTERVAL 130 DAY), (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (3, 'Arjun Yadav',   'male',   '2005-11-21', 'none', NULL,                        'Bhilwara',   'Bhilwara','Rajasthan',   '311001', 'marginalized_youth',     'verified',   1, DATE_SUB(CURDATE(), INTERVAL 100 DAY), (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (4, 'Kamla Kumari',  'female', '1979-01-30', 'none', 'Documents lost in flood',  'Sitapur',    'Sitapur', 'Uttar Pradesh','261001', 'disaster_affected',      'registered', 1, DATE_SUB(CURDATE(), INTERVAL 30 DAY),  (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (5, 'Mohan Lal',     'male',   '1971-09-09', 'none', NULL,                        'Khera',      'Jaipur',  'Rajasthan',   '303001', 'underprivileged_family', 'active',     1, DATE_SUB(CURDATE(), INTERVAL 80 DAY),  (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (6, 'Pooja Kumari',  'female', '2003-05-18', 'none', NULL,                        'Rampur',     'Jaipur',  'Rajasthan',   '303002', 'marginalized_youth',     'verified',   1, DATE_SUB(CURDATE(), INTERVAL 60 DAY),  (SELECT id FROM users WHERE email='pm@satvikdaan.org'),    NOW(), NOW());

INSERT INTO households (beneficiary_id, members_count, monthly_income, latitude, longitude, created_by, created_at, updated_at) VALUES
 (1, 5,  6000.00, 26.912400, 75.787300, (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (2, 4,  5000.00, 26.850000, 75.800000, (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (3, 6,  4500.00, 25.347000, 74.640000, (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (4, 7,  3000.00, 27.570000, 80.680000, (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (5, 5,  7000.00, 26.912400, 75.787300, (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (6, 4,  8000.00, 26.850000, 75.800000, (SELECT id FROM users WHERE email='pm@satvikdaan.org'),    NOW(), NOW());

INSERT INTO enrollments (beneficiary_id, program_id, status, eligibility_score, eligibility_notes, enrolled_on, created_by, created_at, updated_at) VALUES
 (1, 1, 'enrolled',   100, 'All checks passed', DATE_SUB(CURDATE(), INTERVAL 120 DAY), (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (2, 1, 'completed',  100, 'All checks passed', DATE_SUB(CURDATE(), INTERVAL 125 DAY), (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (3, 2, 'enrolled',   100, 'All checks passed', DATE_SUB(CURDATE(), INTERVAL 90 DAY),  (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (4, 4, 'eligible',   100, 'All checks passed', NULL,                                  (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (5, 3, 'enrolled',   100, 'All checks passed', DATE_SUB(CURDATE(), INTERVAL 70 DAY),  (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (5, 4, 'dropped',     80, 'All checks passed', DATE_SUB(CURDATE(), INTERVAL 60 DAY),  (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW());
UPDATE enrollments SET completed_on = DATE_SUB(CURDATE(), INTERVAL 10 DAY) WHERE status='completed';

INSERT INTO aid_deliveries (beneficiary_id, program_id, enrollment_id, delivery_type, amount, description, delivered_on, status, created_by, created_at, updated_at) VALUES
 (1, 1, 1, 'cash',    3000.00, 'Course stipend - month 1',        DATE_SUB(CURDATE(), INTERVAL 90 DAY), 'verified',  (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (1, 1, 1, 'in_kind', 4500.00, 'Sewing machine and starter kit',  DATE_SUB(CURDATE(), INTERVAL 60 DAY), 'delivered', (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (3, 2, 3, 'cash',    5000.00, 'Term fee support',                DATE_SUB(CURDATE(), INTERVAL 45 DAY), 'verified',  (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (5, 3, 5, 'service', NULL,    'Health check-up and medicines',   DATE_SUB(CURDATE(), INTERVAL 30 DAY), 'delivered', (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW()),
 (4, 4, NULL,'in_kind',1800.00,'Dry ration kit (1 month)',        DATE_SUB(CURDATE(), INTERVAL 3 DAY),  'pending',   (SELECT id FROM users WHERE email='field@satvikdaan.org'), NOW(), NOW());

INSERT INTO surveys (beneficiary_id, program_id, survey_type, monthly_income, outcome_score, surveyed_on, created_by, created_at, updated_at) VALUES
 (2, 1, 'baseline', 5000.00,  NULL, DATE_SUB(CURDATE(), INTERVAL 130 DAY), (SELECT id FROM users WHERE email='pm@satvikdaan.org'), NOW(), NOW()),
 (2, 1, 'endline',  9500.00,  82,   DATE_SUB(CURDATE(), INTERVAL 10 DAY),  (SELECT id FROM users WHERE email='pm@satvikdaan.org'), NOW(), NOW()),
 (1, 1, 'baseline', 6000.00,  NULL, DATE_SUB(CURDATE(), INTERVAL 120 DAY), (SELECT id FROM users WHERE email='pm@satvikdaan.org'), NOW(), NOW());
