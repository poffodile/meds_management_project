<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use Illuminate\Support\Facades\DB;

class NotificationTest extends TestCase
{
    protected $user;
    protected $csrfToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('user_name', 'komal')->first();
    }

    protected function actingAsUser()
    {
        return $this->withoutMiddleware(\App\Http\Middleware\checkUserAuth::class)
                     ->actingAs($this->user);
    }

    // 4a. Auth tests

    public function test_list_requires_authentication()
    {
        $response = $this->post('/roster/notifications/list');
        $this->assertNotEquals(200, $response->status());
    }

    public function test_mark_read_requires_authentication()
    {
        $response = $this->post('/roster/notifications/mark-read', ['id' => 1]);
        $this->assertNotEquals(200, $response->status());
    }

    public function test_mark_all_read_requires_authentication()
    {
        $response = $this->post('/roster/notifications/mark-all-read');
        $this->assertNotEquals(200, $response->status());
    }

    public function test_unread_count_requires_authentication()
    {
        $response = $this->post('/roster/notifications/unread-count');
        $this->assertNotEquals(200, $response->status());
    }

    // 4a. Validation tests

    public function test_mark_read_rejects_missing_id()
    {
        $response = $this->actingAsUser()->postJson('/roster/notifications/mark-read', []);
        $response->assertStatus(422);
    }

    public function test_mark_read_rejects_non_integer_id()
    {
        $response = $this->actingAsUser()->postJson('/roster/notifications/mark-read', ['id' => 'abc']);
        $response->assertStatus(422);
    }

    public function test_list_rejects_invalid_type_id()
    {
        $response = $this->actingAsUser()->postJson('/roster/notifications/list', ['type_id' => 'abc']);
        $response->assertStatus(422);
    }

    public function test_list_rejects_invalid_date()
    {
        $response = $this->actingAsUser()->postJson('/roster/notifications/list', ['start_date' => 'not-a-date']);
        $response->assertStatus(422);
    }

    // 4a. Happy path

    public function test_list_returns_notifications()
    {
        $response = $this->actingAsUser()->postJson('/roster/notifications/list', ['page' => 1]);
        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data' => ['notifications', 'total', 'page', 'per_page', 'last_page']]);
        $this->assertTrue($response->json('success'));
    }

    public function test_unread_count_returns_integer()
    {
        $response = $this->actingAsUser()->postJson('/roster/notifications/unread-count');
        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'count']);
        $this->assertIsInt($response->json('count'));
    }

    public function test_notification_page_loads()
    {
        $response = $this->actingAsUser()->get('/roster/notifications');
        $response->assertStatus(200);
    }

    // 4b. Flow test

    public function test_mark_read_then_unread_count_decreases()
    {
        $homeId = (int) explode(',', $this->user->home_id)[0];

        DB::table('notification')
            ->whereRaw('FIND_IN_SET(?, home_id)', [$homeId])
            ->limit(5)
            ->update(['status' => 0]);

        $countBefore = $this->actingAsUser()->postJson('/roster/notifications/unread-count')->json('count');

        $notif = DB::table('notification')
            ->whereRaw('FIND_IN_SET(?, home_id)', [$homeId])
            ->where('status', 0)
            ->first();

        $this->actingAsUser()->postJson('/roster/notifications/mark-read', ['id' => $notif->id]);

        $countAfter = $this->actingAsUser()->postJson('/roster/notifications/unread-count')->json('count');
        $this->assertEquals($countBefore - 1, $countAfter);
    }

    public function test_mark_all_read_zeroes_count()
    {
        $homeId = (int) explode(',', $this->user->home_id)[0];

        DB::table('notification')
            ->whereRaw('FIND_IN_SET(?, home_id)', [$homeId])
            ->limit(3)
            ->update(['status' => 0]);

        $this->actingAsUser()->postJson('/roster/notifications/mark-all-read');

        $count = $this->actingAsUser()->postJson('/roster/notifications/unread-count')->json('count');
        $this->assertEquals(0, $count);

        DB::table('notification')
            ->whereRaw('FIND_IN_SET(?, home_id)', [$homeId])
            ->update(['status' => 0]);
    }

    // 4c. IDOR tests

    public function test_list_does_not_leak_other_home_notifications()
    {
        $homeId = (int) explode(',', $this->user->home_id)[0];

        $response = $this->actingAsUser()->postJson('/roster/notifications/list', ['page' => 1]);
        $notifications = $response->json('data.notifications');

        foreach ($notifications as $n) {
            $record = DB::table('notification')->where('id', $n['id'])->first();
            $this->assertNotNull($record);
            $homeIds = explode(',', $record->home_id);
            $this->assertTrue(in_array((string)$homeId, $homeIds), "Notification {$n['id']} home_id {$record->home_id} does not contain {$homeId}");
        }
    }

    public function test_mark_read_rejects_cross_home_notification()
    {
        $homeId = (int) explode(',', $this->user->home_id)[0];

        $otherNotif = DB::table('notification')
            ->whereRaw('NOT FIND_IN_SET(?, home_id)', [$homeId])
            ->first();

        if (!$otherNotif) {
            $this->markTestSkipped('No cross-home notification to test with');
        }

        $response = $this->actingAsUser()->postJson('/roster/notifications/mark-read', ['id' => $otherNotif->id]);
        $response->assertStatus(404);
    }

    // 4d. Security payload tests

    public function test_xss_in_notification_message_is_escaped()
    {
        $homeId = (int) explode(',', $this->user->home_id)[0];

        $id = DB::table('notification')->insertGetId([
            'home_id' => (string) $homeId,
            'user_id' => $this->user->id,
            'service_user_id' => null,
            'event_id' => 1,
            'notification_event_type_id' => 1,
            'event_action' => 'TEST',
            'message' => '<script>alert("xss")</script>',
            'is_sticky' => 0,
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAsUser()->postJson('/roster/notifications/list', ['page' => 1]);
        $json = $response->getContent();

        $this->assertStringNotContainsString('<script>alert("xss")</script>', $json);

        DB::table('notification')->where('id', $id)->delete();
    }

    public function test_csrf_required_on_post()
    {
        $response = $this->post('/roster/notifications/list', ['page' => 1]);
        $this->assertContains($response->status(), [302, 419]);
    }

    public function test_mark_read_rejects_negative_id()
    {
        $response = $this->actingAsUser()->postJson('/roster/notifications/mark-read', ['id' => -1]);
        $this->assertContains($response->status(), [404, 422]);
    }
}
