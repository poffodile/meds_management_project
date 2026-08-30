<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Section 2.6, part four — authority to reopen a signed-off round.
 *
 * WHY A NEW PERMISSION RATHER THAN AN EXISTING ONE.
 * Reopening reopens the ability to write clinical records into a period a
 * manager has already signed off. That is not the same act as looking at a
 * dashboard, and `view_manager_dashboard` — which is what the old code fell
 * through to — is held by anyone who can see the manager screen at all.
 *
 * Nor is it a correction. `correction_approval` is about changing what a record
 * says; this is about making the period writable again. Overloading it would
 * mean anyone who can approve a correction can also reopen a shift, which
 * nobody decided.
 *
 * THREE ROLES, AND ONLY THREE (owner ruling). Service Manager runs the house
 * and signs off its shifts. Medication Lead owns medication practice and
 * already carries incident review and reconciliation. Organisation Owner
 * carries organisation-wide accountability for the record.
 *
 * Organisation Administrator is deliberately excluded: it administers accounts
 * and structure, and nothing in this repository shows it carrying clinical
 * record accountability. Managing staff is not a reason to reopen a shift.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    private const CODE = 'reopen_medication_round';

    private const ROLES = [
        'Service Manager',
        'Medication Lead',
        'Organisation Owner',
    ];

    public function up(): void
    {
        $db = DB::connection('record7');

        $existing = $db->table('record7_permissions')->where('code', self::CODE)->value('id');

        $permissionId = $existing ?: $db->table('record7_permissions')->insertGetId([
            'code' => self::CODE,
            'name' => 'Reopen a medication round',
            'description' => 'Make a signed-off round writable again, once a request has been approved.',

            // Sensitive: it reopens a period somebody has already signed off.
            'is_sensitive' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (self::ROLES as $roleName) {
            $roleId = $db->table('record7_roles')->where('name', $roleName)->value('id');

            if ($roleId === null) {
                continue;
            }

            $already = $db->table('record7_role_permissions')
                ->where('role_id', $roleId)->where('permission_id', $permissionId)->exists();

            if (! $already) {
                // This pivot carries no timestamps.
                $db->table('record7_role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $db = DB::connection('record7');

        $permissionId = $db->table('record7_permissions')->where('code', self::CODE)->value('id');

        if ($permissionId === null) {
            return;
        }

        $db->table('record7_role_permissions')->where('permission_id', $permissionId)->delete();
        $db->table('record7_permissions')->where('id', $permissionId)->delete();
    }
};
