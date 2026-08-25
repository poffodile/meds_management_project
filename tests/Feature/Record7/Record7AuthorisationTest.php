<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Role;
use App\Services\Record7\AccessPolicy;

/**
 * Section 0.5 — roles, per-user allow and deny rules, competencies, access
 * types and access date windows.
 *
 * Each of the five layers gets its own test, then the combinations that the
 * supplied scenarios actually depend on.
 */
class Record7AuthorisationTest extends Record7TestCase
{
    private function policy(): AccessPolicy
    {
        return app(AccessPolicy::class);
    }

    /* ── Layer 1: account state ──────────────────────────────────────────── */

    public function test_a_suspended_account_is_refused_everything(): void
    {
        $decision = $this->policy()->decide(
            $this->user('ethan.cole'), 'view_dashboard', $this->house('Oakwood House')->id
        );

        $this->assertFalse($decision->allowed);
        $this->assertSame('account_suspended', $decision->code);
    }

    public function test_an_account_outside_its_access_window_is_refused(): void
    {
        $user = $this->user('maya.thompson');
        $user->access_ends_at = now()->subDay();
        $user->save();

        $decision = $this->policy()->decide($user, 'view_dashboard', $this->house('Oakwood House')->id);

        $this->assertFalse($decision->allowed);
        $this->assertSame('account_access_expired', $decision->code);
    }

    /* ── Layer 2: service access ─────────────────────────────────────────── */

    public function test_a_house_the_account_does_not_hold_refuses_everything(): void
    {
        $decision = $this->policy()->decide(
            $this->user('noah.williams'), 'view_dashboard', $this->house('Rosewood House')->id
        );

        $this->assertFalse($decision->allowed);
        $this->assertSame('no_service_access', $decision->code);
    }

    /* ── Layer 3: access type ────────────────────────────────────────────── */

    public function test_a_read_only_grant_can_never_authorise_a_write(): void
    {
        $maya = $this->user('maya.thompson');
        $oakwood = $this->house('Oakwood House')->id;

        foreach (['administer_medication', 'stock_management', 'correction_approval'] as $permission) {
            $decision = $this->policy()->decide($maya, $permission, $oakwood);

            $this->assertFalse($decision->allowed, $permission.' must be refused for review-only access');
            $this->assertSame('read_only_access', $decision->code);
        }

        // But reading is exactly what the reviewer is for.
        $this->assertTrue($this->policy()->allows($maya, 'view_access_audit', $oakwood));
        $this->assertTrue($this->policy()->allows($maya, 'view_people', $oakwood));
    }

    /* ── Layer 4: per-user rules ─────────────────────────────────────────── */

    public function test_an_explicit_allow_grants_beyond_the_role_matrix(): void
    {
        // Support Worker's matrix does not include administering medication.
        $supportWorker = Role::where('code', 'R7')->firstOrFail();
        $this->assertFalse(
            $supportWorker->permissions->contains(fn ($p) => $p->code === 'administer_medication')
        );

        // Olivia holds an explicit allow at both her houses.
        $this->assertTrue($this->policy()->allows(
            $this->user('olivia.carter'), 'administer_medication', $this->house('Oakwood House')->id
        ));
    }

    public function test_an_explicit_deny_beats_everything_below_it(): void
    {
        $decision = $this->policy()->decide(
            $this->user('grace.taylor'), 'administer_medication', $this->house('Rosewood House')->id
        );

        $this->assertFalse($decision->allowed);
        $this->assertSame('explicit_deny', $decision->code);
    }

    public function test_a_rule_scoped_to_one_house_does_not_apply_to_another(): void
    {
        $olivia = $this->user('olivia.carter');

        // The allow at Oakwood is scoped to Oakwood. Remove the Rosewood one
        // and Rosewood must fall back to the role matrix, which refuses.
        $olivia->permissionRules()
            ->where('service_id', $this->house('Rosewood House')->id)
            ->update(['status' => 'revoked']);

        $this->assertTrue($this->policy()->allows($olivia, 'administer_medication', $this->house('Oakwood House')->id));
        $this->assertFalse($this->policy()->allows($olivia, 'administer_medication', $this->house('Rosewood House')->id));
    }

    public function test_an_expired_rule_stops_applying(): void
    {
        $olivia = $this->user('olivia.carter');
        $olivia->permissionRules()->update(['ends_at' => now()->subDay()]);

        $this->assertFalse($this->policy()->allows(
            $olivia, 'administer_medication', $this->house('Oakwood House')->id
        ));
    }

    /* ── Layer 5: competency ─────────────────────────────────────────────── */

    public function test_an_unassessed_competency_blocks_the_action_it_gates(): void
    {
        // Grace has an explicit deny AND no competency. Remove the deny so the
        // competency layer is tested on its own.
        $grace = $this->user('grace.taylor');
        $grace->permissionRules()->update(['status' => 'revoked']);

        // Grant an explicit allow so the role matrix cannot be the refusal.
        $grace->permissionRules()->create([
            'permission_id' => \App\Models\Record7\Permission::where('code', 'administer_medication')->value('id'),
            'service_id' => $this->house('Rosewood House')->id,
            'effect' => 'allow',
            'status' => 'active',
            'reason' => 'Test: isolate the competency layer.',
        ]);

        $decision = $this->policy()->decide(
            $grace, 'administer_medication', $this->house('Rosewood House')->id
        );

        $this->assertFalse($decision->allowed, 'Competency alone must be able to refuse.');
        $this->assertSame('competency_not_assessed', $decision->code);
    }

    public function test_a_current_competency_permits_the_action_it_gates(): void
    {
        $this->assertTrue($this->policy()->allows(
            $this->user('noah.williams'), 'manage_controlled_drugs', $this->house('Oakwood House')->id
        ));
    }

    public function test_an_expired_competency_blocks_the_action(): void
    {
        $noah = $this->user('noah.williams');
        $noah->competencies()->update(['status' => 'expired']);

        $decision = $this->policy()->decide(
            $noah, 'administer_medication', $this->house('Oakwood House')->id
        );

        $this->assertFalse($decision->allowed);
        $this->assertSame('competency_expired', $decision->code);
    }

    public function test_a_competency_due_for_review_still_permits_practice(): void
    {
        // Overdue paperwork must not stop safe practice mid-round.
        $noah = $this->user('noah.williams');
        $noah->competencies()->update(['status' => 'review_due']);

        $this->assertTrue($this->policy()->allows(
            $noah, 'administer_medication', $this->house('Oakwood House')->id
        ));
    }

    /* ── The two roles added for Section 0 ───────────────────────────────── */

    public function test_the_quality_and_compliance_reviewer_is_read_only_with_audit_access(): void
    {
        $role = Role::where('code', 'R8')->firstOrFail();
        $codes = $role->permissions->pluck('code')->all();

        $this->assertContains('view_access_audit', $codes);
        $this->assertNotContains('administer_medication', $codes);
        $this->assertNotContains('manage_controlled_drugs', $codes);
        $this->assertNotContains('manage_staff', $codes);
    }

    public function test_the_external_healthcare_professional_is_least_privilege(): void
    {
        $role = Role::where('code', 'R9')->firstOrFail();
        $codes = $role->permissions->pluck('code')->all();

        $this->assertNotContains('administer_medication', $codes);
        $this->assertNotContains('witness_medication', $codes);
        $this->assertNotContains('manage_controlled_drugs', $codes);
        $this->assertNotContains('view_access_audit', $codes);
        $this->assertNotContains('manage_staff', $codes);

        // Least privilege, not no privilege.
        $this->assertLessThanOrEqual(2, count($codes));
        $this->assertContains('view_people', $codes);
    }

    /* ── Server-side enforcement, not just the interface ─────────────────── */

    public function test_a_support_worker_is_refused_the_audit_screen_by_the_server(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');

        $this->get('/record7/access-audit')->assertForbidden();
    }

    public function test_a_manager_reaches_the_audit_screen(): void
    {
        $this->signInAt('daniel.evans', 'Oakwood House');

        $this->get('/record7/access-audit')->assertOk();
    }
}
