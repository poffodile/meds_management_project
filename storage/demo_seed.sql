-- ============================================================
--  Medication Round DEMO seed (home 1) — reversible.
--  Seeded mar_sheets:    prescriber = 'DEMO_SEED'
--  Seeded care_plan_risks: control_measures = 'DEMO_SEED'
--  Resident edits (allergies/gender/weight): ids 1, 21, 55, 57
--  Cleanup: see storage/demo_seed_cleanup.sql
-- ============================================================

-- Evening-round medications (current round at seed time ~14:18)
INSERT INTO mar_sheets
 (home_id, client_id, medication_name, dosage, dose, route, frequency, time_slots, as_required, prn_details, reason_for_medication, start_date, end_date, is_controlled, cd_schedule, stock_level, reorder_level, storage_requirements, mar_status, discontinued, created_by, is_deleted, prescriber, created_at, updated_at)
VALUES
 (1, 1,  'Amoxicillin',      '250mg',  '1 capsule', 'Oral', 'Three times daily',   '["14:00"]', 0, NULL,         'Take with food',     '2026-06-01', '2026-12-31', 0, NULL,         40, 10, NULL,          'active', 0, 15, 0, 'DEMO_SEED', NOW(), NOW()),
 (1, 19, 'Morphine Sulphate','10mg',   '1 tablet',  'Oral', 'Twice daily',         '["15:00"]', 0, NULL,         'For pain relief',    '2026-06-01', '2026-12-31', 1, 'Schedule 2', 48, 12, 'CD cabinet',  'active', 0, 15, 0, 'DEMO_SEED', NOW(), NOW()),
 (1, 21, 'Omeprazole',       '20mg',   '1 capsule', 'Oral', 'Once daily',          '["14:30"]', 0, NULL,         'Before food',        '2026-06-01', '2026-12-31', 0, NULL,         28, 10, NULL,          'active', 0, 15, 0, 'DEMO_SEED', NOW(), NOW()),
 (1, 21, 'Melatonin',        '2mg',    '1 tablet',  'Oral', 'As required',         '["14:00"]', 1, 'Sleep aid',  'Sleep difficulties', '2026-06-01', '2026-12-31', 0, NULL,         30, 10, NULL,          'active', 0, 15, 0, 'DEMO_SEED', NOW(), NOW()),
 (1, 55, 'Amlodipine',       '5mg',    '1 tablet',  'Oral', 'Once daily',          '["16:00"]', 0, NULL,         'Swallow whole',      '2026-06-01', '2026-12-31', 0, NULL,         60, 15, NULL,          'active', 0, 15, 0, 'DEMO_SEED', NOW(), NOW()),
 (1, 55, 'Vitamin D3',       '1000iu', '1 tablet',  'Oral', 'Once daily',          '["17:00"]', 0, NULL,         NULL,                 '2026-06-01', '2026-12-31', 0, NULL,         90, 20, NULL,          'active', 0, 15, 0, 'DEMO_SEED', NOW(), NOW()),
 (1, 57, 'Atorvastatin',     '20mg',   '1 tablet',  'Oral', 'Once daily at night', '["14:00"]', 0, NULL,         'Take at night',      '2026-06-01', '2026-12-31', 0, NULL,          5, 10, NULL,          'active', 0, 15, 0, 'DEMO_SEED', NOW(), NOW());

-- Resident demographics + allergies (for the safety banner / detail card)
UPDATE service_user SET allergies='Penicillin, Codeine', gender='M', weight=78, weight_unit='kg' WHERE id=1;
UPDATE service_user SET gender='F', weight=65, weight_unit='kg' WHERE id=21;
UPDATE service_user SET allergies='Aspirin', gender='M', weight=82, weight_unit='kg' WHERE id=55;
UPDATE service_user SET gender='M', weight=70, weight_unit='kg' WHERE id=57;

-- Risk flags (surface in the safety banner)
INSERT INTO care_plan_risks
 (home_id, user_id, client_id, overview_id, emergency_information_id, description, likelihood, impact, control_measures, status, created_at, updated_at)
VALUES
 (1, 15, 1,  1, 1, 'Falls Risk', 'Medium', 'High',   'DEMO_SEED', 1, NOW(), NOW()),
 (1, 15, 55, 1, 1, 'Diabetes',   'Low',    'Medium', 'DEMO_SEED', 1, NOW(), NOW());
