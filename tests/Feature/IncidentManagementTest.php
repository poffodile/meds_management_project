<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Models\Staff\StaffReportIncidents;
use Illuminate\Support\Facades\Session;

class IncidentManagementTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('is_deleted', 0)->where('status', 1)->first();
    }

    /** @test */
    public function unauthenticated_user_cannot_access_incident_list()
    {
        $response = $this->get('/roster/incident-management');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function unauthenticated_user_cannot_access_incident_details()
    {
        $response = $this->get('/roster/incident-report-details/1');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function unauthenticated_user_cannot_update_status()
    {
        $response = $this->post('/roster/incident-status-update/1', ['status' => 2]);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_view_incident_list()
    {
        $response = $this->withoutMiddleware()
            ->actingAs($this->user)
            ->get('/roster/incident-management');

        $response->assertStatus(200);
        $response->assertViewIs('frontEnd.roster.incident_management.incident');
    }

    /** @test */
    public function authenticated_user_can_view_own_home_incident()
    {
        $incident = StaffReportIncidents::where('home_id', explode(',', $this->user->home_id)[0])->first();
        if (!$incident) {
            $this->markTestSkipped('No incidents for this user home');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->user)
            ->get('/roster/incident-report-details/' . $incident->id);

        $response->assertStatus(200);
        $response->assertViewIs('frontEnd.roster.incident_management.incident_report_details');
        $response->assertViewHas('incident');
    }

    /** @test */
    public function user_cannot_view_other_home_incident()
    {
        $home_id = explode(',', $this->user->home_id)[0];
        $otherIncident = StaffReportIncidents::where('home_id', '!=', $home_id)->first();
        if (!$otherIncident) {
            $this->markTestSkipped('No incidents from other homes to test multi-tenancy');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->user)
            ->get('/roster/incident-report-details/' . $otherIncident->id);

        $response->assertRedirect('roster/incident-management');
        $response->assertSessionHas('error', 'Incident not found');
    }

    /** @test */
    public function status_update_validates_input()
    {
        $incident = StaffReportIncidents::where('home_id', explode(',', $this->user->home_id)[0])->first();
        if (!$incident) {
            $this->markTestSkipped('No incidents for this user home');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->user)
            ->post('/roster/incident-status-update/' . $incident->id, [
                'status' => 99,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function status_update_works_for_own_home()
    {
        $home_id = explode(',', $this->user->home_id)[0];
        $incident = StaffReportIncidents::where('home_id', $home_id)->where('status', 1)->first();
        if (!$incident) {
            $this->markTestSkipped('No reported incidents to test status update');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->user)
            ->post('/roster/incident-status-update/' . $incident->id, [
                'status' => 2,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify DB updated
        $this->assertEquals(2, $incident->fresh()->status);

        // Reset back to original status
        $incident->status = 1;
        $incident->save();
    }

    /** @test */
    public function status_update_blocked_for_other_home()
    {
        $home_id = explode(',', $this->user->home_id)[0];
        $otherIncident = StaffReportIncidents::where('home_id', '!=', $home_id)->first();
        if (!$otherIncident) {
            $this->markTestSkipped('No incidents from other homes');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->user)
            ->post('/roster/incident-status-update/' . $otherIncident->id, [
                'status' => 2,
            ]);

        $response->assertRedirect('roster/incident-management');
        $response->assertSessionHas('error', 'Incident not found');
    }

    /** @test */
    public function incident_save_validates_required_fields()
    {
        $response = $this->withoutMiddleware()
            ->actingAs($this->user)
            ->post('/roster/incident-report-save', []);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function load_data_returns_json()
    {
        $response = $this->withoutMiddleware()
            ->actingAs($this->user)
            ->post('/roster/incident-report-loadData');

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'data', 'pagination']);
    }
}
