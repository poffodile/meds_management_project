<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Section 2.7, part six — retiring the mutable stock tables.
 *
 * NEITHER TABLE IS DROPPED. Their rows are the pre-2.7 record and they stay
 * readable. What ends is their authority.
 *
 * `record7_stock_levels` becomes read-only. Every balance now comes from the
 * ledger, and a table that could still be written would be a second source of
 * truth by the following week.
 *
 * `record7_stock_events` is retired in part. `count` and `discrepancy` were the
 * old, mutable way of saying the cupboard and the record disagreed, and their
 * `resolved_at` is exactly the acknowledgement-erases-evidence path Section 2.7
 * removes: fixture row 90 is a Senna discrepancy — expected 30, counted 28 —
 * closed with "Found recorded on the wrong chart. Balance corrected at the next
 * count." No balance was corrected and no corrective record exists.
 *
 * `delivery_overdue` STAYS LIVE, and that is not an exception to the rule. It
 * asserts no quantity. The condition it describes is "the pharmacy has not
 * delivered" and the fact that ends it is "it arrived" — a workflow act and the
 * condition genuinely coincide, in a way they never do for a balance. It is
 * explicitly not part of the stock ledger and not part of discrepancy truth.
 *
 * NOTHING IS IMPORTED AND NOTHING IS DELETED. 15 of the 18 rows in each table
 * are reseed artefacts — byte-for-byte duplicates of the live three, stranded
 * because Record7Section0Seeder deletes services with foreign key checks off
 * while these tables clear only by service_id. They are left exactly where they
 * are, unreferenced.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        $db = DB::connection('record7');

        $db->unprepared('DROP TRIGGER IF EXISTS record7_stock_levels_retired_insert');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_stock_levels_retired_insert
            BEFORE INSERT ON record7_stock_levels
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'stock levels are derived from the stock ledger; this table is retired';
            END
        SQL);

        $db->unprepared('DROP TRIGGER IF EXISTS record7_stock_levels_retired_update');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_stock_levels_retired_update
            BEFORE UPDATE ON record7_stock_levels
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'stock levels are derived from the stock ledger; this table is retired';
            END
        SQL);

        $db->unprepared('DROP TRIGGER IF EXISTS record7_stock_events_retired_insert');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_stock_events_retired_insert
            BEFORE INSERT ON record7_stock_events
            FOR EACH ROW
            BEGIN
                IF NEW.kind <> 'delivery_overdue' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'counts and discrepancies are recorded in the stock ledger; only a delivery is recorded here';
                END IF;
            END
        SQL);

        $db->unprepared('DROP TRIGGER IF EXISTS record7_stock_events_retired_update');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_stock_events_retired_update
            BEFORE UPDATE ON record7_stock_events
            FOR EACH ROW
            BEGIN
                IF OLD.kind <> 'delivery_overdue' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'a stock count or discrepancy recorded before Section 2.7 is history and cannot be changed';
                END IF;

                IF NEW.kind <> OLD.kind THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'what a stock event is cannot be changed';
                END IF;

                -- A delivery may still be chased and closed. It asserts no
                -- quantity, so nothing about closing it can make a missing
                -- quantity cease to exist.
            END
        SQL);
    }

    public function down(): void
    {
        $db = DB::connection('record7');

        foreach ([
            'record7_stock_levels_retired_insert',
            'record7_stock_levels_retired_update',
            'record7_stock_events_retired_insert',
            'record7_stock_events_retired_update',
        ] as $trigger) {
            $db->unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }
};
