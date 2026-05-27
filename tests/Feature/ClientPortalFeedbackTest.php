<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Models\ClientPortalAccess;
use App\Models\ClientPortalFeedback;
use Illuminate\Support\Facades\DB;

class ClientPortalFeedbackTest extends TestCase
{
    protected $adminUser;
    protected $portalUser;
    protected $portalAccess;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('user_name', 'komal')->first();
        $this->portalUser = User::where('user_name', 'portal_test')->first();
        $this->portalAccess = ClientPortalAccess::where('user_email', 'portal_test@careone.test')
            ->where('is_deleted', 0)
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

    // ==================== PERMISSION TESTS ====================

    public function test_01_portal_feedback_page_loads_with_permission()
    {
        $response = $this->actingAsPortal()->get('/portal/feedback');
        $response->assertStatus(200);
        $response->assertSee('We Value Your Feedback');
        $response->assertSee('Submit Feedback');
    }

    public function test_02_portal_feedback_denied_without_permission()
    {
        $access = $this->portalAccess;
        $original = $access->can_send_messages;

        try {
            $access->update(['can_send_messages' => 0]);

            $response = $this->actingAsPortal()->get('/portal/feedback');
            $response->assertStatus(200);
            $response->assertSee('Access Denied');
        } finally {
            $access->update(['can_send_messages' => $original]);
        }
    }

    // ==================== SUBMIT FEEDBACK TESTS ====================

    public function test_03_portal_submits_feedback_successfully()
    {
        $response = $this->actingAsPortal()->post('/portal/feedback/submit', [
            'subject' => 'Test feedback submission',
            'comments' => 'This is a test comment for feedback.',
            'feedback_type' => 'general',
            'category' => 'care_quality',
            'rating' => 4,
            'relationship' => 'family',
            'is_anonymous' => 0,
            'wants_callback' => 0,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $feedback = ClientPortalFeedback::where('subject', 'Test feedback submission')->first();
        $this->assertNotNull($feedback);
        $this->assertEquals($this->portalAccess->home_id, $feedback->home_id);
        $this->assertEquals($this->portalAccess->client_id, $feedback->client_id);
        $this->assertEquals($this->portalAccess->id, $feedback->submitted_by_id);
        $this->assertEquals('new', $feedback->status);
        $this->assertEquals(4, $feedback->rating);

        $feedback->delete();
    }

    public function test_04_complaint_auto_sets_high_priority()
    {
        $response = $this->actingAsPortal()->post('/portal/feedback/submit', [
            'subject' => 'Test complaint priority',
            'comments' => 'Testing auto-priority for complaints.',
            'feedback_type' => 'complaint',
            'category' => 'communication',
            'rating' => 2,
            'relationship' => 'family',
        ]);

        $response->assertStatus(200);
        $feedback = ClientPortalFeedback::where('subject', 'Test complaint priority')->first();
        $this->assertNotNull($feedback);
        $this->assertEquals('high', $feedback->priority);

        $feedback->delete();
    }

    public function test_05_invalid_feedback_type_returns_422()
    {
        $response = $this->actingAsPortal()->postJson('/portal/feedback/submit', [
            'subject' => 'Bad type test',
            'comments' => 'Testing invalid type.',
            'feedback_type' => 'invalid_type',
            'category' => 'care_quality',
            'rating' => 3,
            'relationship' => 'family',
        ]);

        $response->assertStatus(422);
    }

    // ==================== IDOR TESTS ====================

    public function test_06_tampered_client_id_ignored()
    {
        $response = $this->actingAsPortal()->post('/portal/feedback/submit', [
            'subject' => 'IDOR test client_id',
            'comments' => 'Attempting to submit with tampered client_id.',
            'feedback_type' => 'general',
            'category' => 'care_quality',
            'rating' => 3,
            'relationship' => 'family',
            'client_id' => 9999,
        ]);

        $response->assertStatus(200);
        $feedback = ClientPortalFeedback::where('subject', 'IDOR test client_id')->first();
        $this->assertNotNull($feedback);
        $this->assertEquals($this->portalAccess->client_id, $feedback->client_id);
        $this->assertNotEquals(9999, $feedback->client_id);

        $feedback->delete();
    }

    public function test_07_tampered_home_id_ignored()
    {
        $response = $this->actingAsPortal()->post('/portal/feedback/submit', [
            'subject' => 'IDOR test home_id',
            'comments' => 'Attempting to submit with tampered home_id.',
            'feedback_type' => 'general',
            'category' => 'care_quality',
            'rating' => 3,
            'relationship' => 'family',
            'home_id' => 9999,
        ]);

        $response->assertStatus(200);
        $feedback = ClientPortalFeedback::where('subject', 'IDOR test home_id')->first();
        $this->assertNotNull($feedback);
        $this->assertEquals($this->portalAccess->home_id, $feedback->home_id);
        $this->assertNotEquals(9999, $feedback->home_id);

        $feedback->delete();
    }

    // ==================== CROSS-CLIENT ISOLATION ====================

    public function test_08_portal_user_sees_only_own_submissions()
    {
        $response = $this->actingAsPortal()->get('/portal/feedback');
        $response->assertStatus(200);

        $content = $response->getContent();
        $feedbackList = ClientPortalFeedback::forHome($this->portalAccess->home_id)
            ->forClient($this->portalAccess->client_id)
            ->where('submitted_by_id', $this->portalAccess->id)
            ->active()
            ->get();

        foreach ($feedbackList as $fb) {
            $this->assertStringContainsString($fb->subject, $content);
        }
    }

    // ==================== ANONYMOUS FEEDBACK ====================

    public function test_09_anonymous_feedback_hides_name_in_admin()
    {
        $response = $this->actingAsAdmin()->get('/roster/feedback-hub/list');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue($data['status']);

        $anonymousFeedback = collect($data['feedback'])->where('is_anonymous', true)->first();
        $this->assertNotNull($anonymousFeedback);
        $this->assertEquals('Anonymous', $anonymousFeedback['submitted_by']);
    }

    // ==================== ADMIN ACKNOWLEDGE ====================

    public function test_10_admin_acknowledges_feedback()
    {
        $feedback = ClientPortalFeedback::where('status', 'new')
            ->where('home_id', 8)
            ->where('is_anonymous', 0)
            ->first();
        $this->assertNotNull($feedback);

        $response = $this->actingAsAdmin()->post('/roster/feedback-hub/acknowledge', [
            'feedback_id' => $feedback->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $feedback->refresh();
        $this->assertEquals('acknowledged', $feedback->status);
        $this->assertNotNull($feedback->acknowledged_by);
        $this->assertNotNull($feedback->acknowledged_date);

        $feedback->update(['status' => 'new', 'acknowledged_by' => null, 'acknowledged_date' => null]);
    }

    // ==================== ADMIN RESPOND ====================

    public function test_11_admin_responds_to_feedback()
    {
        $feedback = ClientPortalFeedback::where('status', 'new')
            ->where('home_id', 8)
            ->where('is_anonymous', 0)
            ->first();
        $this->assertNotNull($feedback);

        $response = $this->actingAsAdmin()->post('/roster/feedback-hub/respond', [
            'feedback_id' => $feedback->id,
            'response' => 'Thank you for your feedback, we will look into this.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $feedback->refresh();
        $this->assertEquals('resolved', $feedback->status);
        $this->assertEquals('Thank you for your feedback, we will look into this.', $feedback->response);
        $this->assertNotNull($feedback->response_date);
        $this->assertNotNull($feedback->responded_by);

        $feedback->update([
            'status' => 'new',
            'response' => null,
            'response_date' => null,
            'responded_by' => null,
            'responded_by_name' => null,
        ]);
    }

    // ==================== ADMIN HOME ISOLATION ====================

    public function test_12_admin_cannot_manage_other_home_feedback()
    {
        $otherFeedback = ClientPortalFeedback::create([
            'home_id' => 999,
            'client_id' => 1,
            'submitted_by' => 'Other User',
            'submitted_by_id' => 999,
            'relationship' => 'family',
            'feedback_type' => 'general',
            'category' => 'care_quality',
            'rating' => 3,
            'subject' => 'Other home feedback',
            'comments' => 'This is from a different home.',
            'priority' => 'medium',
            'status' => 'new',
            'is_deleted' => 0,
        ]);

        $response = $this->actingAsAdmin()->post('/roster/feedback-hub/acknowledge', [
            'feedback_id' => $otherFeedback->id,
        ]);

        $response->assertJson(['status' => false]);

        $otherFeedback->refresh();
        $this->assertEquals('new', $otherFeedback->status);

        $otherFeedback->delete();
    }

    // ==================== STAR RATING VALIDATION ====================

    public function test_13_rating_out_of_range_returns_422()
    {
        $response = $this->actingAsPortal()->postJson('/portal/feedback/submit', [
            'subject' => 'Rating test zero',
            'comments' => 'Testing rating below minimum.',
            'feedback_type' => 'general',
            'category' => 'care_quality',
            'rating' => 0,
            'relationship' => 'family',
        ]);
        $response->assertStatus(422);

        $response = $this->actingAsPortal()->postJson('/portal/feedback/submit', [
            'subject' => 'Rating test six',
            'comments' => 'Testing rating above maximum.',
            'feedback_type' => 'general',
            'category' => 'care_quality',
            'rating' => 6,
            'relationship' => 'family',
        ]);
        $response->assertStatus(422);
    }

    // ==================== AUTH TEST ====================

    public function test_14_unauthenticated_portal_feedback_redirects()
    {
        $response = $this->get('/portal/feedback');
        $response->assertStatus(302);
    }

    // ==================== GDPR - ANONYMOUS NAME CHECK ====================

    public function test_15_non_anonymous_feedback_shows_real_name()
    {
        $response = $this->actingAsAdmin()->get('/roster/feedback-hub/list');
        $response->assertStatus(200);

        $data = $response->json();
        $nonAnon = collect($data['feedback'])->where('is_anonymous', false)->first();
        $this->assertNotNull($nonAnon);
        $this->assertNotEquals('Anonymous', $nonAnon['submitted_by']);
        $this->assertEquals('Jane Smith', $nonAnon['submitted_by']);
    }
}
