<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Models\HandoverLogBook;
use Illuminate\Support\Facades\DB;

class HandoverTest extends TestCase
{
    protected $adminUser;
    protected $staffUser;
    protected $homeId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('user_type', 'A')->where('is_deleted', '0')->first();
        $this->staffUser = User::where('user_type', 'N')->where('is_deleted', '0')->first();
        if ($this->adminUser) {
            $this->homeId = (int) explode(',', $this->adminUser->home_id)[0];
        }
    }

    /**
     * Create a test handover record for the admin user's home.
     */
    private function createTestHandover(): ?HandoverLogBook
    {
        return HandoverLogBook::create([
            'user_id' => $this->adminUser->id,
            'assigned_staff_user_id' => $this->staffUser ? $this->staffUser->id : $this->adminUser->id,
            'service_user_id' => 0,
            'log_book_id' => 0,
            'home_id' => $this->homeId,
            'title' => 'Test Handover ' . time(),
            'details' => 'Test details for unit test',
            'notes' => 'Test notes',
            'date' => now(),
            'is_deleted' => 0,
        ]);
    }

    /**
     * Create a handover record in a different home for IDOR testing.
     */
    private function createCrossHomeHandover(): ?HandoverLogBook
    {
        $otherHomeId = DB::table('home')->where('id', '!=', $this->homeId)->value('id');
        if (!$otherHomeId) return null;

        return HandoverLogBook::create([
            'user_id' => $this->adminUser->id,
            'assigned_staff_user_id' => $this->adminUser->id,
            'service_user_id' => 0,
            'log_book_id' => 0,
            'home_id' => $otherHomeId,
            'title' => 'Cross Home Test ' . time(),
            'details' => 'Should not be accessible',
            'date' => now(),
            'is_deleted' => 0,
        ]);
    }

    // --- Authentication Tests ---

    public function test_handover_list_requires_auth()
    {
        $response = $this->post('/handover/daily/log');
        $response->assertRedirect();
    }

    public function test_handover_edit_requires_auth()
    {
        $response = $this->post('/handover/daily/log/edit', []);
        $response->assertRedirect();
    }

    public function test_handover_to_staff_requires_auth()
    {
        $response = $this->post('/handover/service/log', []);
        $response->assertRedirect();
    }

    public function test_handover_acknowledge_requires_auth()
    {
        $response = $this->post('/handover/acknowledge', []);
        $response->assertRedirect();
    }

    // --- Validation Tests ---

    public function test_edit_validates_required_fields()
    {
        $this->assertNotNull($this->adminUser, 'No admin user found in DB');

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/handover/daily/log/edit', []);

        $response->assertSessionHasErrors(['handover_log_book_id']);
    }

    public function test_edit_validates_field_types()
    {
        $this->assertNotNull($this->adminUser, 'No admin user found in DB');

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/handover/daily/log/edit', [
                'handover_log_book_id' => 'not-an-integer',
                'detail' => str_repeat('x', 5001),
            ]);

        $response->assertSessionHasErrors(['handover_log_book_id', 'detail']);
    }

    public function test_handover_to_staff_validates_required_fields()
    {
        $this->assertNotNull($this->adminUser, 'No admin user found in DB');

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/handover/service/log', []);

        $response->assertSessionHasErrors(['log_id', 'staff_user_id', 'servc_use_id']);
    }

    public function test_acknowledge_validates_required_fields()
    {
        $this->assertNotNull($this->adminUser, 'No admin user found in DB');

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/handover/acknowledge', []);

        $response->assertSessionHasErrors(['handover_log_book_id']);
    }

    // --- Happy Path Tests ---

    public function test_handover_list_returns_records()
    {
        $this->assertNotNull($this->adminUser, 'No admin user found in DB');

        $handover = $this->createTestHandover();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/handover/daily/log');

        $response->assertStatus(200);

        // Clean up
        if ($handover) {
            $handover->forceDelete();
        }
    }

    public function test_edit_updates_record()
    {
        $this->assertNotNull($this->adminUser, 'No admin user found in DB');

        $handover = $this->createTestHandover();
        $this->assertNotNull($handover, 'Failed to create test handover');

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/handover/daily/log/edit', [
                'handover_log_book_id' => $handover->id,
                'detail' => 'Updated details',
                'notes' => 'Updated notes',
            ]);

        $response->assertStatus(200);
        $response->assertSee('1');

        $handover->refresh();
        $this->assertEquals('Updated details', $handover->details);
        $this->assertEquals('Updated notes', $handover->notes);

        // Clean up
        $handover->forceDelete();
    }

    public function test_acknowledge_marks_handover()
    {
        $this->assertNotNull($this->adminUser, 'No admin user found in DB');

        $handover = $this->createTestHandover();
        $this->assertNotNull($handover, 'Failed to create test handover');

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/handover/acknowledge', [
                'handover_log_book_id' => $handover->id,
            ]);

        $response->assertStatus(200);
        $response->assertSee('1');

        $handover->refresh();
        $this->assertNotNull($handover->acknowledged_at);
        $this->assertEquals($this->adminUser->id, $handover->acknowledged_by);

        // Clean up
        $handover->forceDelete();
    }

    // --- IDOR / Multi-Tenancy Tests ---

    public function test_edit_rejects_cross_home_record()
    {
        $this->assertNotNull($this->adminUser, 'No admin user found in DB');

        $crossHomeHandover = $this->createCrossHomeHandover();
        if (!$crossHomeHandover) {
            $this->markTestSkipped('No other home available for IDOR test');
        }

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/handover/daily/log/edit', [
                'handover_log_book_id' => $crossHomeHandover->id,
                'detail' => 'Should not work',
                'notes' => 'IDOR attempt',
            ]);

        $response->assertStatus(200);
        // Should return "0" (record not found for this home)
        $response->assertSee('0');

        // Verify record was NOT modified
        $crossHomeHandover->refresh();
        $this->assertNotEquals('Should not work', $crossHomeHandover->details);

        // Clean up
        $crossHomeHandover->forceDelete();
    }

    public function test_acknowledge_rejects_cross_home_record()
    {
        $this->assertNotNull($this->adminUser, 'No admin user found in DB');

        $crossHomeHandover = $this->createCrossHomeHandover();
        if (!$crossHomeHandover) {
            $this->markTestSkipped('No other home available for IDOR test');
        }

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/handover/acknowledge', [
                'handover_log_book_id' => $crossHomeHandover->id,
            ]);

        $response->assertStatus(200);
        $response->assertSee('0');

        // Verify not acknowledged
        $crossHomeHandover->refresh();
        $this->assertNull($crossHomeHandover->acknowledged_at);

        // Clean up
        $crossHomeHandover->forceDelete();
    }

    // --- IDOR: Cross-home logbook reference ---

    public function test_handover_to_staff_rejects_cross_home_logbook()
    {
        $this->assertNotNull($this->adminUser, 'No admin user found in DB');

        // Find a logbook entry from a different home
        $otherHomeId = \Illuminate\Support\Facades\DB::table('home')
            ->where('id', '!=', $this->homeId)->value('id');
        if (!$otherHomeId) {
            $this->markTestSkipped('No other home for IDOR test');
        }

        $otherLogBook = \Illuminate\Support\Facades\DB::table('log_book')
            ->where('home_id', $otherHomeId)
            ->where('is_deleted', '0')
            ->first();

        if (!$otherLogBook) {
            $this->markTestSkipped('No logbook entry in other home for IDOR test');
        }

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/handover/service/log', [
                'log_id' => $otherLogBook->id,
                'staff_user_id' => $this->adminUser->id,
                'servc_use_id' => 1,
            ]);

        $response->assertStatus(200);
        // Should return "0" because the logbook entry doesn't belong to this user's home
        $response->assertSee('0');
    }

    // --- XSS Tests ---

    public function test_xss_payload_stored_safely()
    {
        $this->assertNotNull($this->adminUser, 'No admin user found in DB');

        $handover = $this->createTestHandover();
        $this->assertNotNull($handover, 'Failed to create test handover');

        $xssPayload = '<script>alert(1)</script>';

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/handover/daily/log/edit', [
                'handover_log_book_id' => $handover->id,
                'detail' => $xssPayload,
                'notes' => $xssPayload,
            ]);

        $response->assertStatus(200);

        // Verify stored as-is (not double-encoded)
        $handover->refresh();
        $this->assertEquals($xssPayload, $handover->details);

        // Verify rendered escaped in list view
        $listResponse = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/handover/daily/log');

        // The HTML output should contain the escaped version, not the raw script tag
        $content = $listResponse->getContent();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $content);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $content);

        // Clean up
        $handover->forceDelete();
    }

    // --- Soft Delete Tests ---

    public function test_deleted_records_not_shown_in_list()
    {
        $this->assertNotNull($this->adminUser, 'No admin user found in DB');

        $handover = $this->createTestHandover();
        $this->assertNotNull($handover, 'Failed to create test handover');

        // Soft-delete the record
        $handover->is_deleted = 1;
        $handover->save();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/handover/daily/log');

        $content = $response->getContent();
        $this->assertStringNotContainsString($handover->title, $content);

        // Clean up
        $handover->forceDelete();
    }
}
