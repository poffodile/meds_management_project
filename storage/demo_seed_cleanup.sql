-- Reverts storage/demo_seed.sql. Run:  mysql laravel < storage/demo_seed_cleanup.sql
DELETE FROM mar_sheets             WHERE home_id=1 AND prescriber='DEMO_SEED';
DELETE FROM care_plan_risks        WHERE home_id=1 AND control_measures='DEMO_SEED';
DELETE FROM controlled_drug_register WHERE home_id=1 AND notes LIKE '%DEMO_SEED%';
UPDATE service_user SET allergies=NULL, gender=NULL, weight=NULL, weight_unit='kg' WHERE id IN (1, 21, 55, 57);
