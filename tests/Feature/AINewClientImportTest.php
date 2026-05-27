<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AINewClientImportTest extends TestCase
{
    private $user;
    private $homeId = 8;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('id', 194)->first();
    }

    protected function tearDown(): void
    {
        DB::table('ai_document_imports')
            ->where('import_type', 'new_client')
            ->where('home_id', $this->homeId)
            ->delete();

        DB::table('service_user')
            ->where('home_id', $this->homeId)
            ->where('short_description', 'Imported via AI Document Import')
            ->delete();

        parent::tearDown();
    }

    private function actAsUser()
    {
        return $this->actingAs($this->user)->withoutMiddleware();
    }

    private function createTestPdf(): UploadedFile
    {
        $pdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<</Font<</F1 4 0 R>>>>>>endobj\n4 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\nxref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000246 00000 n \ntrailer<</Size 5/Root 1 0 R>>\nstartxref\n318\n%%EOF";
        return UploadedFile::fake()->createWithContent('test_client.pdf', $pdfContent);
    }

    // === Upload Tests ===

    public function test_upload_single_pdf_returns_success(): void
    {
        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/upload', [
                'files' => [
                    UploadedFile::fake()->create('client_info.pdf', 100, 'application/pdf'),
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true])
            ->assertJsonStructure(['import_id', 'files_count', 'filenames']);

        $this->assertEquals(1, $response->json('files_count'));
    }

    public function test_upload_multiple_files_returns_success(): void
    {
        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/upload', [
                'files' => [
                    UploadedFile::fake()->create('doc1.pdf', 100, 'application/pdf'),
                    UploadedFile::fake()->create('doc2.pdf', 200, 'application/pdf'),
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);
        $this->assertEquals(2, $response->json('files_count'));
    }

    public function test_upload_rejects_invalid_file_type(): void
    {
        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/upload', [
                'files' => [
                    UploadedFile::fake()->create('malicious.php', 100, 'application/x-php'),
                ],
            ]);

        $response->assertStatus(422);
    }

    public function test_upload_rejects_oversized_file(): void
    {
        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/upload', [
                'files' => [
                    UploadedFile::fake()->create('huge.pdf', 11000, 'application/pdf'),
                ],
            ]);

        $response->assertStatus(422);
    }

    public function test_upload_requires_authentication(): void
    {
        $response = $this->postJson('/roster/ai-new-client-import/upload', [
            'files' => [UploadedFile::fake()->create('test.pdf', 100, 'application/pdf')],
        ]);

        $response->assertStatus(302);
    }

    public function test_upload_requires_at_least_one_file(): void
    {
        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/upload', [
                'files' => [],
            ]);

        $response->assertStatus(422);
    }

    // === Extract Tests (Mock OpenAI) ===

    public function test_extract_fails_when_import_not_found(): void
    {
        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/extract', [
                'import_id' => 99999,
            ]);

        $response->assertStatus(422)
            ->assertJson(['status' => false]);
    }

    public function test_extract_fails_when_import_already_processed(): void
    {
        $importId = DB::table('ai_document_imports')->insertGetId([
            'home_id' => $this->homeId,
            'client_id' => null,
            'import_type' => 'new_client',
            'uploaded_by' => $this->user->id,
            'original_filename' => json_encode(['test.pdf']),
            'stored_path' => json_encode(['imports/8/test.pdf']),
            'file_size' => 1000,
            'file_mime' => 'application/pdf',
            'import_status' => 'extracted',
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/extract', [
                'import_id' => $importId,
            ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['status' => false]);

        DB::table('ai_document_imports')->where('id', $importId)->delete();
    }

    public function test_extract_fails_when_token_cap_exceeded(): void
    {
        $importId = $this->createUploadedImport();

        DB::table('ai_usage_logs')->insert([
            'home_id' => $this->homeId,
            'user_id' => $this->user->id,
            'feature' => 'new_client_import',
            'model_used' => 'gpt-4o',
            'tokens_input' => 90000,
            'tokens_output' => 15000,
            'tokens_total' => 105000,
            'response_status' => 'success',
            'created_at' => now(),
        ]);

        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/extract', [
                'import_id' => $importId,
            ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['status' => false]);

        DB::table('ai_usage_logs')
            ->where('home_id', $this->homeId)
            ->where('tokens_input', 90000)
            ->delete();
    }

    public function test_extract_handles_invalid_file_gracefully(): void
    {
        $importId = $this->createUploadedImport();

        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/extract', [
                'import_id' => $importId,
            ]);

        $response->assertStatus(422)
            ->assertJson(['status' => false]);
    }

    // === Confirm / Client Creation Tests ===

    public function test_confirm_creates_new_service_user(): void
    {
        $importId = $this->createExtractedImport();

        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/confirm', [
                'import_id' => $importId,
                'selected_categories' => [],
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true])
            ->assertJsonStructure(['client_id', 'client_name', 'redirect_url']);

        $clientId = $response->json('client_id');
        $client = DB::table('service_user')->where('id', $clientId)->first();

        $this->assertNotNull($client);
        $this->assertEquals($this->homeId, $client->home_id);
        $this->assertEquals('Susanna Rose Craven', $client->name);
        $this->assertEquals(0, $client->is_deleted);
    }

    public function test_confirm_creates_client_with_all_required_fields(): void
    {
        $importId = $this->createExtractedImport();

        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/confirm', [
                'import_id' => $importId,
                'selected_categories' => [],
            ]);

        $clientId = $response->json('client_id');
        $client = DB::table('service_user')->where('id', $clientId)->first();

        $this->assertNotEmpty($client->user_name);
        $this->assertEquals(0, $client->department);
        $this->assertEquals('', $client->section);
        $this->assertEquals('cm', $client->height_unit);
        $this->assertEquals('kg', $client->weight_unit);
        $this->assertEquals('', $client->hair_and_eyes);
        $this->assertEquals('', $client->markings);
        $this->assertEquals('', $client->image);
        $this->assertNotEmpty($client->password);
        $this->assertNotNull($client->created_at);
    }

    public function test_confirm_imports_care_history(): void
    {
        $importId = $this->createExtractedImport();

        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/confirm', [
                'import_id' => $importId,
                'selected_categories' => ['care_history'],
            ]);

        $clientId = $response->json('client_id');
        $count = DB::table('su_care_history')
            ->where('service_user_id', $clientId)
            ->where('home_id', $this->homeId)
            ->count();

        $this->assertEquals(1, $count);
        $this->assertEquals(1, $response->json('summary.care_history'));

        DB::table('su_care_history')->where('service_user_id', $clientId)->delete();
    }

    public function test_confirm_imports_medications(): void
    {
        $importId = $this->createExtractedImport();

        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/confirm', [
                'import_id' => $importId,
                'selected_categories' => ['medications'],
            ]);

        $clientId = $response->json('client_id');
        $count = DB::table('mar_sheets')
            ->where('client_id', $clientId)
            ->where('home_id', $this->homeId)
            ->count();

        $this->assertEquals(1, $count);

        DB::table('mar_sheets')->where('client_id', $clientId)->delete();
    }

    public function test_confirm_imports_risk_assessments(): void
    {
        $importId = $this->createExtractedImport();

        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/confirm', [
                'import_id' => $importId,
                'selected_categories' => ['risk_assessments'],
            ]);

        $clientId = $response->json('client_id');
        $count = DB::table('su_risk')
            ->where('service_user_id', $clientId)
            ->where('home_id', $this->homeId)
            ->count();

        $this->assertEquals(1, $count);

        DB::table('su_risk')->where('service_user_id', $clientId)->delete();
        DB::table('risk')->where('description', 'Absconding')->where('home_id', $this->homeId)->delete();
    }

    public function test_confirm_with_no_categories_creates_only_client(): void
    {
        $importId = $this->createExtractedImport();

        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/confirm', [
                'import_id' => $importId,
                'selected_categories' => [],
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);

        $clientId = $response->json('client_id');
        $this->assertNotNull($clientId);

        $careHistory = DB::table('su_care_history')->where('service_user_id', $clientId)->count();
        $this->assertEquals(0, $careHistory);
    }

    // === IDOR Tests ===

    public function test_idor_extract_from_different_home_fails(): void
    {
        $importId = DB::table('ai_document_imports')->insertGetId([
            'home_id' => 999,
            'client_id' => null,
            'import_type' => 'new_client',
            'uploaded_by' => 1,
            'original_filename' => json_encode(['test.pdf']),
            'stored_path' => json_encode(['imports/999/test.pdf']),
            'file_size' => 1000,
            'file_mime' => 'application/pdf',
            'import_status' => 'uploaded',
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/extract', [
                'import_id' => $importId,
            ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['status' => false]);

        DB::table('ai_document_imports')->where('id', $importId)->delete();
    }

    public function test_idor_confirm_from_different_home_fails(): void
    {
        $importId = DB::table('ai_document_imports')->insertGetId([
            'home_id' => 999,
            'client_id' => null,
            'import_type' => 'new_client',
            'uploaded_by' => 1,
            'original_filename' => json_encode(['test.pdf']),
            'stored_path' => json_encode(['imports/999/test.pdf']),
            'file_size' => 1000,
            'file_mime' => 'application/pdf',
            'import_status' => 'extracted',
            'extracted_data' => json_encode(['client' => ['full_name' => 'Test']]),
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/confirm', [
                'import_id' => $importId,
                'selected_categories' => [],
            ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['status' => false]);

        DB::table('ai_document_imports')->where('id', $importId)->delete();
    }

    // === Security Tests ===

    public function test_mass_assignment_home_id_ignored(): void
    {
        $importId = $this->createExtractedImport();

        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/confirm', [
                'import_id' => $importId,
                'selected_categories' => [],
                'home_id' => 999,
            ]);

        $clientId = $response->json('client_id');
        $client = DB::table('service_user')->where('id', $clientId)->first();

        $this->assertEquals($this->homeId, $client->home_id);
    }

    public function test_generated_username_is_unique(): void
    {
        $importId1 = $this->createExtractedImport();
        $response1 = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/confirm', [
                'import_id' => $importId1,
                'selected_categories' => [],
            ]);
        $clientId1 = $response1->json('client_id');

        $importId2 = $this->createExtractedImport();
        $response2 = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/confirm', [
                'import_id' => $importId2,
                'selected_categories' => [],
            ]);
        $clientId2 = $response2->json('client_id');

        $user1 = DB::table('service_user')->where('id', $clientId1)->value('user_name');
        $user2 = DB::table('service_user')->where('id', $clientId2)->value('user_name');

        $this->assertNotEquals($user1, $user2);
    }

    public function test_confirm_rejects_invalid_categories(): void
    {
        $importId = $this->createExtractedImport();

        $response = $this->actAsUser()
            ->postJson('/roster/ai-new-client-import/confirm', [
                'import_id' => $importId,
                'selected_categories' => ['malicious_category', 'care_history'],
            ]);

        $response->assertStatus(422);
    }

    // === Helper Methods ===

    private function createUploadedImport(): int
    {
        $filePath = "imports/{$this->homeId}/test_" . time() . '.pdf';

        $fullDir = storage_path('app/private/imports/' . $this->homeId);
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }
        file_put_contents(storage_path('app/private/' . $filePath), '%PDF-1.4 test content with enough characters to pass the length check and make sure extraction works properly for the AI system');

        return DB::table('ai_document_imports')->insertGetId([
            'home_id' => $this->homeId,
            'client_id' => null,
            'import_type' => 'new_client',
            'uploaded_by' => $this->user->id,
            'original_filename' => json_encode(['test_client.pdf']),
            'stored_path' => json_encode([$filePath]),
            'file_size' => 1000,
            'file_mime' => 'application/pdf',
            'import_status' => 'uploaded',
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createExtractedImport(): int
    {
        return DB::table('ai_document_imports')->insertGetId([
            'home_id' => $this->homeId,
            'client_id' => null,
            'import_type' => 'new_client',
            'uploaded_by' => $this->user->id,
            'original_filename' => json_encode(['test_client.pdf']),
            'stored_path' => json_encode(["imports/{$this->homeId}/test.pdf"]),
            'file_size' => 1000,
            'file_mime' => 'application/pdf',
            'import_status' => 'extracted',
            'extracted_data' => json_encode([
                'client' => [
                    'full_name' => 'Susanna Rose Craven',
                    'date_of_birth' => '2018-03-15',
                    'gender' => 'Female',
                    'phone' => '07700123456',
                    'address' => ['street' => '12 Oak Street', 'city' => 'Liverpool', 'postcode' => 'L15 4AB'],
                    'emergency_contact' => ['name' => 'Jane Craven', 'phone' => '07700999888', 'relationship' => 'Mother'],
                    'care_needs' => ['Personal care support', 'Medication management'],
                    'medical_notes' => 'Asthma, mild learning difficulties',
                    'allergies' => 'None known',
                    'mobility' => 'Independent with supervision',
                    'funding_type' => 'Local authority',
                    'local_authority' => 'Sefton Council',
                    'mental_health_issues' => 'Anxiety',
                    'drug_n_alcohol_issues' => 'None',
                    'personal_info' => 'Child in care',
                    'child_type' => 'residential',
                ],
                'care_history' => [['title' => 'Placement', 'date' => '2024-06-01', 'description' => 'Placed at Neptune House.']],
                'medications' => [['medication_name' => 'Salbutamol Inhaler', 'dosage' => '100mcg', 'dose' => '2 puffs', 'route' => 'Inhaled', 'frequency' => 'As required', 'reason_for_medication' => 'Asthma']],
                'risk_assessments' => [['risk_type' => 'Absconding', 'risk_level' => 'high', 'description' => 'History of absconding', 'control_measures' => '1:1 supervision']],
                'body_map' => [],
                'dols' => [],
                'document_summary' => 'Test analysis of client documents.',
            ]),
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
