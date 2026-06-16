-- Controlled Drugs register DEMO seed (home 1). Tagged with [DEMO_SEED] in notes for cleanup.
INSERT INTO controlled_drug_register
 (home_id, client_id, client_name, mar_sheet_id, medication_name, cd_schedule, action_type, entry_date, entry_time, dose_quantity, unit, balance_before, balance_after, witness_name, notes, created_by_user_id, created_at, updated_at)
VALUES
 (1, 19, 'Mick', NULL, 'Morphine Sulphate', 'Schedule 2', 'received',     '2026-06-09', '09:00', 50, 'tablets', 0,  50, 'Jane Brown', 'Stock received from pharmacy [DEMO_SEED]', 15, NOW(), NOW()),
 (1, 19, 'Mick', NULL, 'Morphine Sulphate', 'Schedule 2', 'administered', '2026-06-10', '08:05', 1,  'tablets', 50, 49, 'Sarah Lee',  '[DEMO_SEED]', 15, NOW(), NOW()),
 (1, 19, 'Mick', NULL, 'Morphine Sulphate', 'Schedule 2', 'administered', '2026-06-10', '20:10', 1,  'tablets', 49, 48, 'Tom Hill',   '[DEMO_SEED]', 15, NOW(), NOW()),
 (1, 21, 'Rock', NULL, 'Diazepam',          'Schedule 4', 'received',     '2026-06-08', '10:00', 20, 'mg',      0,  20, 'Sarah Lee',  'New supply [DEMO_SEED]', 15, NOW(), NOW()),
 (1, 21, 'Rock', NULL, 'Diazepam',          'Schedule 4', 'administered', '2026-06-11', '08:00', 2,  'mg',      20, 18, 'Jane Brown', '[DEMO_SEED]', 15, NOW(), NOW());
