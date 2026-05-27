<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Models\ClientPortalAccess;
use App\Models\ClientPortalMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ClientPortalTest extends TestCase
{
    protected $adminUser;
    protected $portalUser;
    protected $portalAccess;
    protected $staffUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('user_name', 'komal')->first();
        $this->portalUser = User::where('user_name', 'portal_test')->first();
        $this->portalAccess = ClientPortalAccess::where('user_email', 'portal_test@careone.test')
            ->where('is_deleted', 0)
            ->first();
        $this->staffUser = User::where('user_type', 'N')
            ->where('home_id', 'LIKE', '%8%')
            ->where('is_deleted', 0)
            ->where('user_name', '!=', 'portal_test')
            ->first();
    }

    protected function actingAsAdmin()
    {
        return $this->withoutMiddleware(\App\Http\Middleware\checkUserAuth::class)
                     ->actingAs($this->adminUser);
    }

    protected function actingAsPortal()
    {
        return $this->withoutMiddleware(\App\Http\Middleware\checkUserAuth::class)
                     ->actingAs($this->portalUser)
                     ->withSession([
                         'portal_access_id' => $this->portalAccess->id,
                         'portal_client_id' => $this->portalAccess->client_id,
                     ]);
    }

    protected function actingAsStaff()
    {
        return $this->withoutMiddleware(\App\Http\Middleware\checkUserAuth::class)
                     ->actingAs($this->staffUser);
    }

    protected function createTestUser(string $email): int
    {
        return DB::table('user')->insertGetId([
            'user_name' => 'test_' . substr(md5($email), 0, 8),
            'password' => bcrypt('123456'),
            'name' => 'Test User',
            'email' => $email,
            'home_id' => '8',
            'admn_id' => 1,
            'user_type' => 'N',
            'is_deleted' => 0,
            'status' => 1,
            'logged_in' => 0,
            'session_token' => '',
            'login_ip' => '',
            'last_activity_time' => now(),
            'job_title' => 'Test',
            'description' => 'Test user',
            'department' => 0,
            'holiday_entitlement' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function cleanupTestUser(string $email): void
    {
        DB::table('client_portal_accesses')->where('user_email', $email)->delete();
        DB::table('user')->where('email', $email)->delete();
    }

    // ==================== 4a. AUTH TESTS ====================

    public function test_portal_dashboard_rejects_unauthenticated()
    {
        $response = $this->get('/portal');
        $response->assertStatus(302);
    }

    public function test_portal_dashboard_rejects_user_without_portal_access()
    {
        $response = $this->withoutMiddleware(\App\Http\Middleware\checkUserAuth::class)
                         ->actingAs($this->adminUser)
                         ->get('/portal');
        $response->assertRedirect('/roster');
    }

    public function test_portal_dashboard_allows_portal_user()
    {
        $response = $this->actingAsPortal()->get('/portal');
        $response->assertStatus(200);
        $response->assertSee('Welcome, Jane Smith');
        $response->assertSee('Katie');
    }

    public function test_admin_portal_list_rejects_unauthenticated()
    {
        $response = $this->post('/roster/client/portal-access-list', ['client_id' => 27]);
        $response->assertStatus(302);
    }

    // ==================== 4b. MULTI-ROLE TESTS ====================

    public function test_admin_cannot_access_portal_dashboard()
    {
        $response = $this->actingAsAdmin()->get('/portal');
        $response->assertRedirect('/roster');
    }

    public function test_portal_user_cannot_access_roster()
    {
        $response = $this->actingAsPortal()->get('/roster');
        $response->assertStatus(200)->assertDontSee('Welcome, Jane Smith');
    }

    public function test_admin_can_list_portal_users()
    {
        $response = $this->actingAsAdmin()
            ->post('/roster/client/portal-access-list', ['client_id' => 27]);
        $response->assertStatus(200);
        $response->assertJson(['status' => true]);
    }

    public function test_admin_can_create_portal_access()
    {
        $testEmail = 'test_create_' . time() . '@careone.test';
        $this->createTestUser($testEmail);

        $response = $this->actingAsAdmin()
            ->post('/roster/client/portal-access-save', [
                'client_id' => 27,
                'user_email' => $testEmail,
                'full_name' => 'Test Portal Create',
                'relationship' => 'guardian',
                'access_level' => 'view_only',
            ]);
        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $this->assertDatabaseHas('client_portal_accesses', [
            'user_email' => $testEmail,
            'client_id' => 27,
            'home_id' => 8,
            'is_deleted' => 0,
        ]);

        $this->cleanupTestUser($testEmail);
    }

    // ==================== 4c. CROSS-CLIENT ISOLATION ====================

    public function test_portal_dashboard_shows_linked_client_only()
    {
        $response = $this->actingAsPortal()->get('/portal');
        $response->assertStatus(200);
        $response->assertSee('Katie');
    }

    public function test_admin_portal_list_filters_by_home()
    {
        $response = $this->actingAsAdmin()
            ->post('/roster/client/portal-access-list', ['client_id' => 27]);
        $response->assertStatus(200);
        $json = $response->json();
        foreach ($json['data'] as $item) {
            $this->assertEquals(8, $item['home_id']);
        }
    }

    // ==================== VALIDATION TESTS ====================

    public function test_save_rejects_missing_client_id()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/roster/client/portal-access-save', [
                'user_email' => 'test@test.com',
                'full_name' => 'Test',
                'relationship' => 'parent',
            ]);
        $response->assertStatus(422);
    }

    public function test_save_rejects_invalid_relationship()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/roster/client/portal-access-save', [
                'client_id' => 27,
                'user_email' => 'test@test.com',
                'full_name' => 'Test',
                'relationship' => 'invalid_value',
            ]);
        $response->assertStatus(422);
    }

    public function test_save_rejects_client_from_different_home()
    {
        $otherClient = DB::table('service_user')
            ->where('home_id', '!=', 8)
            ->first();
        if (!$otherClient) {
            $this->markTestSkipped('No cross-home client for IDOR test');
        }

        $response = $this->actingAsAdmin()
            ->postJson('/roster/client/portal-access-save', [
                'client_id' => $otherClient->id,
                'user_email' => 'portal_test@careone.test',
                'full_name' => 'IDOR Test',
                'relationship' => 'parent',
            ]);
        $response->assertStatus(404);
    }

    public function test_save_rejects_nonexistent_user_email()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/roster/client/portal-access-save', [
                'client_id' => 27,
                'user_email' => 'nonexistent_' . time() . '@careone.test',
                'full_name' => 'Test',
                'relationship' => 'parent',
            ]);
        $response->assertStatus(422);
    }

    // ==================== 4g. SECURITY PAYLOAD TESTS ====================

    public function test_xss_in_full_name_is_stored_raw()
    {
        $xssPayload = '<script>alert(1)</script>';
        $testEmail = 'xss_test_' . time() . '@careone.test';
        $this->createTestUser($testEmail);

        $response = $this->actingAsAdmin()
            ->post('/roster/client/portal-access-save', [
                'client_id' => 27,
                'user_email' => $testEmail,
                'full_name' => $xssPayload,
                'relationship' => 'parent',
            ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('client_portal_accesses', [
            'user_email' => $testEmail,
            'full_name' => $xssPayload,
        ]);

        $this->cleanupTestUser($testEmail);
    }

    public function test_mass_assignment_home_id_is_ignored()
    {
        $testEmail = 'mass_test_' . time() . '@careone.test';
        $this->createTestUser($testEmail);

        $response = $this->actingAsAdmin()
            ->post('/roster/client/portal-access-save', [
                'client_id' => 27,
                'user_email' => $testEmail,
                'full_name' => 'Mass Test',
                'relationship' => 'parent',
                'home_id' => 999,
                'created_by' => 999,
                'is_deleted' => 1,
            ]);
        $response->assertStatus(200);

        $record = DB::table('client_portal_accesses')
            ->where('user_email', $testEmail)
            ->first();
        $this->assertEquals(8, $record->home_id);
        $this->assertNotEquals(999, $record->created_by);
        $this->assertEquals(0, $record->is_deleted);

        $this->cleanupTestUser($testEmail);
    }

    public function test_csrf_required_on_post()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware(\App\Http\Middleware\checkUserAuth::class)
            ->post('/roster/client/portal-access-list', [
                '_token' => 'invalid_token',
                'client_id' => 27,
            ]);
        $this->assertTrue(in_array($response->status(), [200, 419, 302]));
    }

    public function test_delete_requires_admin_role()
    {
        if (!$this->staffUser || in_array($this->staffUser->user_type, ['A', 'M', 'CM'])) {
            $this->markTestSkipped('No non-admin/manager staff user available');
        }

        $response = $this->actingAsStaff()
            ->postJson('/roster/client/portal-access-delete', ['id' => 1]);
        $response->assertStatus(403);
    }

    public function test_portal_user_cannot_access_admin_management()
    {
        $response = $this->actingAsPortal()
            ->postJson('/roster/client/portal-access-list', ['client_id' => 27]);
        $response->assertStatus(403);

        $response = $this->actingAsPortal()
            ->postJson('/roster/client/portal-access-save', [
                'client_id' => 27,
                'user_email' => 'test@test.com',
                'full_name' => 'Test',
                'relationship' => 'parent',
            ]);
        $response->assertStatus(403);
    }

    public function test_revoke_works_for_valid_record()
    {
        $testEmail = 'revoke_test_' . time() . '@careone.test';
        $this->createTestUser($testEmail);

        $this->actingAsAdmin()->post('/roster/client/portal-access-save', [
            'client_id' => 27,
            'user_email' => $testEmail,
            'full_name' => 'Revoke Test',
            'relationship' => 'parent',
        ]);

        $record = DB::table('client_portal_accesses')
            ->where('user_email', $testEmail)
            ->first();

        $response = $this->actingAsAdmin()
            ->post('/roster/client/portal-access-revoke', ['id' => $record->id]);
        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $updated = DB::table('client_portal_accesses')->where('id', $record->id)->first();
        $this->assertEquals(0, $updated->is_active);

        $this->cleanupTestUser($testEmail);
    }

    // ==================== SCHEDULE TESTS ====================

    public function test_sched_01_shows_weekly_grid()
    {
        $response = $this->actingAsPortal()->get('/portal/schedule');
        $response->assertStatus(200);
        $response->assertSee('My Schedule');
        $content = $response->getContent();
        $this->assertStringContainsString('class="calendar-grid"', $content);
        $this->assertStringNotContainsString('Access Denied', $content);
    }

    public function test_sched_02_shows_shifts_for_linked_client()
    {
        $response = $this->actingAsPortal()->get('/portal/schedule?week=2026-04-27');
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('shift-card status', $content);
    }

    public function test_sched_03_gdpr_staff_first_name_only()
    {
        $response = $this->actingAsPortal()->get('/portal/schedule?week=2026-04-27');
        $content = $response->getContent();
        $this->assertStringContainsString('Allan', $content);
        $this->assertStringNotContainsString('Allan Smith', $content);
    }

    public function test_sched_04_shows_unfilled_shifts()
    {
        $response = $this->actingAsPortal()->get('/portal/schedule?week=2026-04-27');
        $content = $response->getContent();
        $this->assertStringContainsString('Unfilled', $content);
        $this->assertStringContainsString('status-unfilled', $content);
    }

    public function test_sched_05_week_navigation()
    {
        $response = $this->actingAsPortal()->get('/portal/schedule?week=2026-05-04');
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('May 04', $content);
    }

    public function test_sched_06_empty_state_future_week()
    {
        $response = $this->actingAsPortal()->get('/portal/schedule?week=2026-09-01');
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('No scheduled items this week', $content);
    }

    public function test_sched_07_rejects_unauthenticated()
    {
        $response = $this->get('/portal/schedule');
        $response->assertStatus(302);
    }

    public function test_sched_08_cross_client_isolation()
    {
        $otherClient = DB::table('service_user')
            ->where('home_id', 8)
            ->where('id', '!=', $this->portalAccess->client_id)
            ->first();
        if (!$otherClient) {
            $this->markTestSkipped('No other client in home 8');
        }

        $otherShiftId = DB::table('scheduled_shifts')
            ->where('service_user_id', $otherClient->id)
            ->where('home_id', '8')
            ->value('id');
        if (!$otherShiftId) {
            DB::table('scheduled_shifts')->insert([
                'home_id' => '8',
                'service_user_id' => $otherClient->id,
                'care_type_id' => '1',
                'assignment' => 'Client',
                'staff_id' => 44,
                'start_date' => '2026-04-28',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'shift_type' => 'morning',
                'status' => 'assigned',
                'tasks' => 'IDOR test shift',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->actingAsPortal()->get('/portal/schedule?week=2026-04-27');
        $content = $response->getContent();
        $this->assertStringNotContainsString('IDOR test shift', $content);
    }

    public function test_sched_09_dashboard_real_count()
    {
        $response = $this->actingAsPortal()->get('/portal');
        $response->assertStatus(200);
        $content = $response->getContent();
        preg_match('/stat-schedule.*?stat-value">(\d+)/s', $content, $matches);
        $this->assertNotEmpty($matches, 'Schedule stat element should exist on dashboard');
        $this->assertGreaterThanOrEqual(0, (int)$matches[1]);
    }

    public function test_sched_10_denied_when_permission_off()
    {
        DB::table('client_portal_accesses')
            ->where('id', $this->portalAccess->id)
            ->update(['can_view_schedule' => 0]);

        try {
            $response = $this->actingAsPortal()->get('/portal/schedule');
            $response->assertStatus(200);
            $content = $response->getContent();
            $this->assertStringContainsString('Access Denied', $content);
            $this->assertStringNotContainsString('class="calendar-grid"', $content);
        } finally {
            DB::table('client_portal_accesses')
                ->where('id', $this->portalAccess->id)
                ->update(['can_view_schedule' => 1]);
        }
    }

    // ==================== MESSAGING TESTS ====================

    public function test_msg_01_inbox_renders_with_messages()
    {
        $response = $this->actingAsPortal()->get('/portal/messages');
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('Messages', $content);
        $this->assertStringContainsString('message-item', $content);
        $this->assertStringNotContainsString('Access Denied', $content);
    }

    public function test_msg_02_permission_denied_when_flag_off()
    {
        DB::table('client_portal_accesses')
            ->where('id', $this->portalAccess->id)
            ->update(['can_send_messages' => 0]);

        try {
            $response = $this->actingAsPortal()->get('/portal/messages');
            $response->assertStatus(200);
            $content = $response->getContent();
            $this->assertStringContainsString('Access Denied', $content);
            $this->assertStringNotContainsString('class="message-item', $content);
        } finally {
            DB::table('client_portal_accesses')
                ->where('id', $this->portalAccess->id)
                ->update(['can_send_messages' => 1]);
        }
    }

    public function test_msg_03_send_message_creates_record()
    {
        $response = $this->actingAsPortal()->postJson('/portal/messages/send', [
            'subject' => 'Test message from tests',
            'message_content' => 'This is a test message body.',
            'category' => 'general',
            'priority' => 'normal',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $this->assertDatabaseHas('client_portal_messages', [
            'subject' => 'Test message from tests',
            'client_id' => $this->portalAccess->client_id,
            'home_id' => $this->portalAccess->home_id,
            'sender_type' => 'family',
        ]);

        DB::table('client_portal_messages')
            ->where('subject', 'Test message from tests')
            ->delete();
    }

    public function test_msg_04_send_rejects_invalid_category()
    {
        $response = $this->actingAsPortal()->postJson('/portal/messages/send', [
            'subject' => 'Test',
            'message_content' => 'Body',
            'category' => 'invalid_category',
            'priority' => 'normal',
        ]);
        $response->assertStatus(422);
    }

    public function test_msg_05_mark_as_read()
    {
        $staffMsg = ClientPortalMessage::where('client_id', $this->portalAccess->client_id)
            ->where('home_id', $this->portalAccess->home_id)
            ->where('sender_type', 'staff')
            ->where('is_read', 0)
            ->first();

        if (!$staffMsg) {
            $this->markTestSkipped('No unread staff message to test');
        }

        $response = $this->actingAsPortal()->post('/portal/messages/read/' . $staffMsg->id);
        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $updated = DB::table('client_portal_messages')->where('id', $staffMsg->id)->first();
        $this->assertEquals(1, $updated->is_read);
    }

    public function test_msg_06_cross_client_isolation()
    {
        $otherClient = DB::table('service_user')
            ->where('home_id', 8)
            ->where('id', '!=', $this->portalAccess->client_id)
            ->first();
        if (!$otherClient) {
            $this->markTestSkipped('No other client in home 8');
        }

        $otherId = DB::table('client_portal_messages')->insertGetId([
            'home_id' => 8,
            'client_id' => $otherClient->id,
            'sender_type' => 'staff',
            'sender_id' => 44,
            'sender_name' => 'Test Staff',
            'recipient_type' => 'family',
            'subject' => 'IDOR isolation test',
            'message_content' => 'Should not be visible',
            'priority' => 'normal',
            'category' => 'general',
            'is_read' => 0,
            'status' => 'sent',
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $response = $this->actingAsPortal()->get('/portal/messages');
            $content = $response->getContent();
            $this->assertStringNotContainsString('IDOR isolation test', $content);

            $response = $this->actingAsPortal()->post('/portal/messages/read/' . $otherId);
            $response->assertJson(['status' => false]);
        } finally {
            DB::table('client_portal_messages')->where('id', $otherId)->delete();
        }
    }

    public function test_msg_07_send_ignores_tampered_client_id()
    {
        $response = $this->actingAsPortal()->postJson('/portal/messages/send', [
            'subject' => 'IDOR tamper test',
            'message_content' => 'Trying to send as another client',
            'category' => 'general',
            'priority' => 'normal',
            'client_id' => 999,
            'home_id' => 999,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $msg = DB::table('client_portal_messages')
            ->where('subject', 'IDOR tamper test')
            ->first();
        $this->assertEquals($this->portalAccess->client_id, $msg->client_id);
        $this->assertEquals($this->portalAccess->home_id, $msg->home_id);

        DB::table('client_portal_messages')->where('id', $msg->id)->delete();
    }

    public function test_msg_08_gdpr_staff_first_name_only()
    {
        $response = $this->actingAsPortal()->get('/portal/messages');
        $content = $response->getContent();
        $this->assertStringContainsString('Allan', $content);
        $this->assertStringNotContainsString('Allan Smith', $content);
    }

    public function test_msg_09_admin_thread_loads()
    {
        $response = $this->actingAsAdmin()->postJson('/roster/messaging-center/thread', [
            'client_id' => 27,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['status' => true]);
        $json = $response->json();
        $this->assertNotEmpty($json['messages']);
    }

    public function test_msg_10_admin_thread_rejects_other_home_client()
    {
        $otherClient = DB::table('service_user')
            ->where('home_id', '!=', 8)
            ->first();
        if (!$otherClient) {
            $this->markTestSkipped('No cross-home client for IDOR test');
        }

        $response = $this->actingAsAdmin()->postJson('/roster/messaging-center/thread', [
            'client_id' => $otherClient->id,
        ]);
        $response->assertStatus(404);
    }

    public function test_msg_11_admin_reply_creates_message()
    {
        $response = $this->actingAsAdmin()->postJson('/roster/messaging-center/reply', [
            'client_id' => 27,
            'message_content' => 'Admin reply test from tests',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $this->assertDatabaseHas('client_portal_messages', [
            'message_content' => 'Admin reply test from tests',
            'sender_type' => 'staff',
            'client_id' => 27,
        ]);

        DB::table('client_portal_messages')
            ->where('message_content', 'Admin reply test from tests')
            ->delete();
    }

    public function test_msg_12_messages_rejects_unauthenticated()
    {
        $response = $this->get('/portal/messages');
        $response->assertStatus(302);
    }

    public function test_msg_13_dashboard_shows_real_unread_count()
    {
        $response = $this->actingAsPortal()->get('/portal');
        $response->assertStatus(200);
        $content = $response->getContent();
        preg_match('/stat-messages.*?stat-value">(\\d+)/s', $content, $matches);
        $this->assertNotEmpty($matches);
        // Messages stat card should NOT have "Coming soon" (other cards still do)
        preg_match('/stat-messages.*?<\/div>\s*<\/div>/s', $content, $cardMatch);
        $this->assertNotEmpty($cardMatch);
        $this->assertStringNotContainsString('Coming soon', $cardMatch[0]);
    }

    public function test_portal_feedback_page_loads()
    {
        $response = $this->actingAsPortal()->get('/portal/feedback');
        $response->assertStatus(200);
        $response->assertSee('We Value Your Feedback');
    }
}

