<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Models\ScheduledReport;
use App\Services\ScheduledReportService;
use Illuminate\Support\Facades\Mail;

class ScheduledReportTest extends TestCase
{
    protected $adminUser;
    protected $portalUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('user_name', 'komal')->first();
        $this->portalUser = User::where('user_name', 'portal_test')->first();
    }

    protected function actingAsAdmin()
    {
        return $this->withoutMiddleware(\App\Http\Middleware\checkUserAuth::class)
                     ->actingAs($this->adminUser);
    }

    protected function tearDown(): void
    {
        ScheduledReport::query()->delete();
        parent::tearDown();
    }

    protected function makeScheduleData(array $overrides = []): array
    {
        return array_merge([
            'report_name' => 'Test Schedule',
            'report_type' => 'training',
            'schedule_frequency' => 'weekly',
            'schedule_day' => 1,
            'schedule_time' => '08:00',
            'recipients' => 'test@example.com',
            'output_format' => 'csv',
            'is_active' => 1,
            'notes' => '',
        ], $overrides);
    }

    // 1. Report page loads with tab bar
    public function test_01_report_page_shows_tabs()
    {
        $response = $this->actingAsAdmin()->get('/roster/reports');
        $response->assertStatus(200);
        $response->assertSee('Generate Report');
        $response->assertSee('Scheduled Reports');
        $response->assertSee('scheduleModal');
    }

    // 2. Schedule list returns empty for home with no schedules
    public function test_02_schedule_list_empty()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/roster/reports/schedules');
        $response->assertStatus(200);
        $response->assertJson(['status' => true, 'schedules' => []]);
    }

    // 3. Create schedule returns 200 and appears in list
    public function test_03_create_schedule()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/roster/reports/schedule/store', $this->makeScheduleData());
        $response->assertStatus(200);
        $response->assertJson(['status' => true]);
        $this->assertNotNull($response->json('schedule.id'));
        $this->assertEquals('Test Schedule', $response->json('schedule.report_name'));

        $list = $this->actingAsAdmin()->getJson('/roster/reports/schedules');
        $list->assertJsonCount(1, 'schedules');
    }

    // 4. Create validates required fields
    public function test_04_create_validates_required()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/roster/reports/schedule/store', [
                'report_type' => 'training',
                'schedule_frequency' => 'weekly',
                'schedule_time' => '08:00',
                'recipients' => 'test@example.com',
                'output_format' => 'csv',
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('report_name');
    }

    // 5. Create validates report_type in allowed list
    public function test_05_create_validates_report_type()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/roster/reports/schedule/store', $this->makeScheduleData([
                'report_type' => 'invalid_type',
            ]));
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('report_type');
    }

    // 6. Create validates recipients format
    public function test_06_create_validates_recipients()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/roster/reports/schedule/store', $this->makeScheduleData([
                'recipients' => '',
            ]));
        $response->assertStatus(422);
    }

    // 7. Update schedule
    public function test_07_update_schedule()
    {
        $create = $this->actingAsAdmin()
            ->postJson('/roster/reports/schedule/store', $this->makeScheduleData());
        $id = $create->json('schedule.id');

        $response = $this->actingAsAdmin()
            ->postJson('/roster/reports/schedule/update', $this->makeScheduleData([
                'id' => $id,
                'report_name' => 'Updated Name',
                'schedule_frequency' => 'daily',
            ]));
        $response->assertStatus(200);
        $this->assertEquals('Updated Name', $response->json('schedule.report_name'));
        $this->assertEquals('daily', $response->json('schedule.schedule_frequency'));
    }

    // 8. Toggle schedule active
    public function test_08_toggle_schedule()
    {
        $create = $this->actingAsAdmin()
            ->postJson('/roster/reports/schedule/store', $this->makeScheduleData());
        $id = $create->json('schedule.id');
        $this->assertTrue($create->json('schedule.is_active'));

        $toggle = $this->actingAsAdmin()
            ->postJson('/roster/reports/schedule/toggle', ['id' => $id]);
        $toggle->assertStatus(200);
        $this->assertFalse($toggle->json('schedule.is_active'));

        $toggle2 = $this->actingAsAdmin()
            ->postJson('/roster/reports/schedule/toggle', ['id' => $id]);
        $this->assertTrue($toggle2->json('schedule.is_active'));
    }

    // 9. Delete schedule (soft delete)
    public function test_09_delete_schedule()
    {
        $create = $this->actingAsAdmin()
            ->postJson('/roster/reports/schedule/store', $this->makeScheduleData());
        $id = $create->json('schedule.id');

        $delete = $this->actingAsAdmin()
            ->postJson('/roster/reports/schedule/delete', ['id' => $id]);
        $delete->assertStatus(200);

        $list = $this->actingAsAdmin()->getJson('/roster/reports/schedules');
        $list->assertJsonCount(0, 'schedules');

        $this->assertEquals(1, ScheduledReport::withoutGlobalScopes()->find($id)->is_deleted);
    }

    // 10. Home isolation: admin only sees own home's schedules
    public function test_10_home_isolation()
    {
        $this->actingAsAdmin()
            ->postJson('/roster/reports/schedule/store', $this->makeScheduleData());

        ScheduledReport::forceCreate([
            'report_name' => 'Other Home Schedule',
            'report_type' => 'incidents',
            'schedule_frequency' => 'daily',
            'schedule_time' => '08:00',
            'recipients' => ['other@example.com'],
            'output_format' => 'csv',
            'home_id' => 999,
            'created_by' => 1,
            'next_run_date' => now()->addDay(),
        ]);

        $list = $this->actingAsAdmin()->getJson('/roster/reports/schedules');
        $list->assertJsonCount(1, 'schedules');
        $this->assertEquals(8, $list->json('schedules.0.home_id'));
    }

    // 11. IDOR: admin cannot update/delete schedule from another home
    public function test_11_idor_protection()
    {
        $other = ScheduledReport::forceCreate([
            'report_name' => 'Other Home Schedule',
            'report_type' => 'incidents',
            'schedule_frequency' => 'daily',
            'schedule_time' => '08:00',
            'recipients' => ['other@example.com'],
            'output_format' => 'csv',
            'home_id' => 999,
            'created_by' => 1,
            'next_run_date' => now()->addDay(),
        ]);

        $update = $this->actingAsAdmin()
            ->postJson('/roster/reports/schedule/update', $this->makeScheduleData([
                'id' => $other->id,
            ]));
        $update->assertStatus(404);

        $delete = $this->actingAsAdmin()
            ->postJson('/roster/reports/schedule/delete', ['id' => $other->id]);
        $delete->assertStatus(404);

        $toggle = $this->actingAsAdmin()
            ->postJson('/roster/reports/schedule/toggle', ['id' => $other->id]);
        $toggle->assertStatus(404);
    }

    // 12. Artisan command dispatches due reports
    public function test_12_artisan_dispatches_due_reports()
    {
        Mail::fake();

        ScheduledReport::forceCreate([
            'report_name' => 'Due Report',
            'report_type' => 'training',
            'schedule_frequency' => 'daily',
            'schedule_time' => '08:00',
            'recipients' => ['test@example.com'],
            'output_format' => 'csv',
            'home_id' => 8,
            'created_by' => 194,
            'is_active' => 1,
            'next_run_date' => now()->subHour(),
        ]);

        $this->artisan('reports:dispatch')
            ->assertExitCode(0);

        Mail::assertSent(\App\Mail\ScheduledReportMail::class, 1);

        $schedule = ScheduledReport::where('report_name', 'Due Report')->first();
        $this->assertEquals('success', $schedule->last_run_status);
        $this->assertNotNull($schedule->last_run_date);
    }

    // 13. Artisan command skips inactive schedules
    public function test_13_artisan_skips_inactive()
    {
        Mail::fake();

        ScheduledReport::forceCreate([
            'report_name' => 'Inactive Report',
            'report_type' => 'training',
            'schedule_frequency' => 'daily',
            'schedule_time' => '08:00',
            'recipients' => ['test@example.com'],
            'output_format' => 'csv',
            'home_id' => 8,
            'created_by' => 194,
            'is_active' => 0,
            'next_run_date' => now()->subHour(),
        ]);

        $this->artisan('reports:dispatch')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    // 14. Artisan command updates next_run_date after execution
    public function test_14_artisan_advances_next_run()
    {
        Mail::fake();

        $schedule = ScheduledReport::forceCreate([
            'report_name' => 'Advance Test',
            'report_type' => 'shifts',
            'schedule_frequency' => 'weekly',
            'schedule_time' => '08:00',
            'recipients' => ['test@example.com'],
            'output_format' => 'csv',
            'home_id' => 8,
            'created_by' => 194,
            'is_active' => 1,
            'next_run_date' => now()->subHour(),
        ]);

        $originalNextRun = $schedule->next_run_date;

        $this->artisan('reports:dispatch')->assertExitCode(0);

        $schedule->refresh();
        $this->assertTrue($schedule->next_run_date->gt($originalNextRun));
    }

    // 15. Unauthenticated redirects
    public function test_15_unauthenticated_redirects()
    {
        $this->get('/roster/reports/schedules')->assertStatus(302);
        $this->post('/roster/reports/schedule/store')->assertStatus(302);
        $this->post('/roster/reports/schedule/update')->assertStatus(302);
        $this->post('/roster/reports/schedule/toggle')->assertStatus(302);
        $this->post('/roster/reports/schedule/delete')->assertStatus(302);
    }
}
