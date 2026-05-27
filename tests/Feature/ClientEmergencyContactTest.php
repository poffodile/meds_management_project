<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Models\ServiceUserEmergencyContact;
use Illuminate\Support\Facades\DB;

class ClientEmergencyContactTest extends TestCase
{
    protected $adminUser;
    protected $clientId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('user_type', 'A')->where('is_deleted', '0')->first();
        $this->clientId = DB::table('service_user')->where('is_deleted', 0)->value('id');
    }

    public function test_emergency_contact_save_requires_auth()
    {
        $response = $this->postJson('/roster/client/emergency-contact/save', [
            'service_user_id' => $this->clientId,
            'contacts' => []
        ]);
        $this->assertTrue(
            $response->status() === 401 ||
            $response->status() === 302 ||
            $response->status() === 419
        );
    }

    public function test_emergency_contact_delete_requires_auth()
    {
        $response = $this->postJson('/roster/client/emergency-contact/delete', [
            'id' => 1
        ]);
        $this->assertTrue(
            $response->status() === 401 ||
            $response->status() === 302 ||
            $response->status() === 419
        );
    }

    public function test_emergency_contact_save_creates_and_updates_and_deletes_contacts()
    {
        if (!$this->adminUser || !$this->clientId) {
            $this->markTestSkipped('Admin user or service user not found');
        }

        // 1. Create new contacts
        $payload = [
            'service_user_id' => $this->clientId,
            'contacts' => [
                [
                    'name' => 'John Doe',
                    'phone_no' => '1234567890',
                    'relationship' => 'Brother'
                ],
                [
                    'name' => 'Jane Smith',
                    'phone_no' => '0987654321',
                    'relationship' => 'Sister'
                ]
            ]
        ];

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/emergency-contact/save', $payload);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // Assert they are in the database
        $this->assertDatabaseHas('service_user_emergency_contacts', [
            'service_user_id' => $this->clientId,
            'name' => 'John Doe',
            'relationship' => 'Brother'
        ]);
        $this->assertDatabaseHas('service_user_emergency_contacts', [
            'service_user_id' => $this->clientId,
            'name' => 'Jane Smith',
            'relationship' => 'Sister'
        ]);

        $contacts = ServiceUserEmergencyContact::where('service_user_id', $this->clientId)->get();
        $this->assertCount(2, $contacts);

        $firstContact = $contacts->where('name', 'John Doe')->first();
        $secondContact = $contacts->where('name', 'Jane Smith')->first();

        // 2. Update first contact and omit the second (which should delete it)
        $updatePayload = [
            'service_user_id' => $this->clientId,
            'contacts' => [
                [
                    'id' => $firstContact->id,
                    'name' => 'John Doe Updated',
                    'phone_no' => '1111111111',
                    'relationship' => 'Brother Updated'
                ],
                [
                    'name' => 'New Guy',
                    'phone_no' => '2222222222',
                    'relationship' => 'Friend'
                ]
            ]
        ];

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/emergency-contact/save', $updatePayload);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // Assert database updates and deletions
        $this->assertDatabaseHas('service_user_emergency_contacts', [
            'id' => $firstContact->id,
            'name' => 'John Doe Updated',
            'relationship' => 'Brother Updated'
        ]);
        $this->assertDatabaseMissing('service_user_emergency_contacts', [
            'id' => $secondContact->id
        ]);
        $this->assertDatabaseHas('service_user_emergency_contacts', [
            'service_user_id' => $this->clientId,
            'name' => 'New Guy',
            'relationship' => 'Friend'
        ]);

        // Clean up
        ServiceUserEmergencyContact::where('service_user_id', $this->clientId)->delete();
    }

    public function test_emergency_contact_delete_action()
    {
        if (!$this->adminUser || !$this->clientId) {
            $this->markTestSkipped('Admin user or service user not found');
        }

        // Create a contact
        $contact = ServiceUserEmergencyContact::create([
            'service_user_id' => $this->clientId,
            'name' => 'To Delete',
            'phone_no' => '12345',
            'relationship' => 'Enemy'
        ]);

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/emergency-contact/delete', [
                'id' => $contact->id
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('service_user_emergency_contacts', [
            'id' => $contact->id
        ]);
    }
}
