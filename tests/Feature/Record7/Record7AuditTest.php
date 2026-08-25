<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\AccessAuditEvent;
use App\Services\Record7\AuditRecorder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Section 0.10 — append-only access auditing and the manager audit screen.
 */
class Record7AuditTest extends Record7TestCase
{
    /* ── Append-only, proven three ways ──────────────────────────────────── */

    public function test_the_model_refuses_to_update_an_audit_event(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');

        $event = AccessAuditEvent::latest('id')->firstOrFail();

        $this->expectException(RuntimeException::class);
        $event->update(['reason' => 'rewritten']);
    }

    public function test_the_model_refuses_to_delete_an_audit_event(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');

        $event = AccessAuditEvent::latest('id')->firstOrFail();

        $this->expectException(RuntimeException::class);
        $event->delete();
    }

    public function test_the_database_itself_refuses_an_update_that_bypasses_the_model(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');

        $id = AccessAuditEvent::latest('id')->value('id');

        // Straight to the query builder, around Eloquent entirely. The trigger
        // is what stops this, which is the guarantee that actually matters.
        $this->expectException(QueryException::class);
        DB::connection('record7')->table('record7_access_audit_events')
            ->where('id', $id)->update(['reason' => 'rewritten']);
    }

    public function test_the_database_itself_refuses_a_delete_that_bypasses_the_model(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');

        $id = AccessAuditEvent::latest('id')->value('id');

        $this->expectException(QueryException::class);
        DB::connection('record7')->table('record7_access_audit_events')->where('id', $id)->delete();
    }

    /* ── What gets recorded ──────────────────────────────────────────────── */

    public function test_a_full_journey_leaves_a_readable_trail(): void
    {
        $before = AccessAuditEvent::max('id') ?? 0;

        $this->signInAt('daniel.evans', 'Oakwood House');
        $this->post('/record7/lock');
        $this->post('/record7/unlock', ['password' => self::PASSWORDS['daniel.evans']]);
        $this->post('/record7/houses', ['house_id' => $this->house('Rosewood House')->id]);
        $this->post('/record7/sign-out');

        $types = AccessAuditEvent::where('id', '>', $before)->pluck('event_type')->all();

        foreach ([
            'password_verified', 'sign_in', 'house_selected',
            'session_locked', 'session_unlocked', 'house_switched', 'sign_out',
        ] as $expected) {
            $this->assertContains($expected, $types, "The trail must include {$expected}.");
        }
    }

    public function test_each_event_snapshots_the_name_and_role_at_the_time(): void
    {
        $this->signInAt('daniel.evans', 'Oakwood House');

        $event = AccessAuditEvent::where('event_type', 'sign_in')
            ->where('event_result', 'success')->latest('id')->firstOrFail();

        $this->assertSame('Daniel Evans', $event->staff_name_at_time);
        $this->assertSame('Service Manager', $event->role_name_at_time);

        // Renaming the person must not rewrite history.
        $user = $this->user('daniel.evans');
        $user->full_name = 'Daniel Evans-Smith';
        $user->save();

        $this->assertSame('Daniel Evans', $event->fresh()->staff_name_at_time);
    }

    public function test_the_events_form_an_unbroken_chain(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');
        $this->post('/record7/sign-out');

        $this->assertSame([], app(AuditRecorder::class)->brokenLinks());
    }

    public function test_a_refused_action_is_recorded_with_its_reason(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');

        $this->get('/record7/access-audit')->assertForbidden();

        $event = AccessAuditEvent::where('event_type', 'permission_denied')->latest('id')->firstOrFail();

        $this->assertSame('denied', $event->event_result);
        $this->assertSame('view_access_audit', $event->metadata['permission']);
        $this->assertSame('role_does_not_allow', $event->metadata['decision']);
    }

    /* ── The manager screen ──────────────────────────────────────────────── */

    public function test_a_support_worker_cannot_open_the_audit_screen(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');
        $this->get('/record7/access-audit')->assertForbidden();
    }

    public function test_a_manager_sees_the_audit_screen(): void
    {
        $this->signInAt('daniel.evans', 'Oakwood House');

        $this->get('/record7/access-audit')
            ->assertOk()
            ->assertSee('Audit', false)
            ->assertSee('Sign in', false);
    }

    public function test_the_reviewer_sees_the_audit_screen_despite_read_only_access(): void
    {
        $this->signInAt('maya.thompson', 'Oakwood House');
        $this->get('/record7/access-audit')->assertOk();
    }

    public function test_opening_the_audit_screen_is_itself_recorded(): void
    {
        $this->signInAt('daniel.evans', 'Oakwood House');
        $this->get('/record7/access-audit');

        $this->assertTrue(
            AccessAuditEvent::where('event_type', 'access_audit_viewed')
                ->where('user_id', $this->user('daniel.evans')->id)->exists()
        );
    }

    public function test_a_manager_cannot_filter_to_a_house_they_do_not_hold(): void
    {
        // Daniel holds Oakwood and Rosewood, not Meadow View.
        $this->signInAt('daniel.evans', 'Oakwood House');

        $this->get('/record7/access-audit?house='.$this->house('Meadow View')->id)
            ->assertForbidden();
    }

    public function test_the_audit_screen_shows_only_houses_the_reader_can_reach(): void
    {
        $this->signInAt('daniel.evans', 'Oakwood House');

        $content = $this->get('/record7/access-audit')->getContent();

        $this->assertStringContainsString('Oakwood House', $content);
        $this->assertStringContainsString('Rosewood House', $content);
        $this->assertStringNotContainsString('Meadow View', $content);
    }
}
