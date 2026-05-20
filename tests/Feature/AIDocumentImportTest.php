<?php

namespace Tests\Feature;

use App\Models\AIDocumentImport;
use App\Services\AI\AIDocumentImportService;
use App\Services\AI\OpenAIService;
use App\Services\AI\PIIFilter;
use App\Services\AI\TokenTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AIDocumentImportTest extends TestCase
{
    private int $homeId = 8;
    private $user;
    private int $clientId;
    private int $maxImportIdBefore;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = \App\User::where('is_deleted', 0)->first();
        if ($this->user) {
            $homeIds = explode(',', $this->user->home_id);
            $this->homeId = (int) ($homeIds[0] ?? 1);
        } else {
            $this->homeId = 1;
        }

        $client = DB::table('service_user')
            ->where('home_id', $this->homeId)
            ->where('is_deleted', 0)
            ->first();

        $this->clientId = $client ? $client->id : 1;

        $this->maxImportIdBefore = (int) DB::table('ai_document_imports')->max('id');
    }

    protected function tearDown(): void
    {
        DB::table('ai_document_imports')->where('id', '>', $this->maxImportIdBefore)->delete();
        parent::tearDown();
    }

    private function actAsUser()
    {
        return $this->actingAs($this->user)->withoutMiddleware();
    }

    private function createRealPdf(): UploadedFile
    {
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n4 0 obj\n<< /Length 178 >>\nstream\nBT\n/F1 12 Tf\n50 700 Td\n(Patient Care Report for John Smith) Tj\n0 -20 Td\n(Medications: Paracetamol 500mg twice daily oral. Omeprazole 20mg once daily.) Tj\n0 -20 Td\n(Risk Assessment: Falls Risk High. Requires walking frame.) Tj\nET\nendstream\nendobj\nxref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000306 00000 n \n0000000233 00000 n \ntrailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n536\n%%EOF";

        $tmpPath = tempnam(sys_get_temp_dir(), 'test_pdf_');
        file_put_contents($tmpPath, $pdfContent);

        return new UploadedFile($tmpPath, 'test_care_report.pdf', 'application/pdf', null, true);
    }

    private function createImportRecord(string $status = 'uploaded', ?array $extractedData = null): AIDocumentImport
    {
        return AIDocumentImport::create([
            'home_id' => $this->homeId,
            'client_id' => $this->clientId,
            'uploaded_by' => $this->user->id,
            'original_filename' => 'test_report.pdf',
            'stored_path' => 'imports/' . $this->homeId . '/test_file.pdf',
            'file_size' => 1024,
            'file_mime' => 'application/pdf',
            'extracted_text_length' => 500,
            'import_status' => $status,
            'extracted_data' => $extractedData,
        ]);
    }

    // ─── Auth Tests ─────────────────────────────────────────

    public function test_upload_requires_auth()
    {
        $response = $this->postJson('/roster/ai-document-import/upload', [
            'client_id' => $this->clientId,
        ]);
        $response->assertStatus(302);
    }

    public function test_extract_requires_auth()
    {
        $response = $this->postJson('/roster/ai-document-import/extract', [
            'import_id' => 1,
        ]);
        $response->assertStatus(302);
    }

    public function test_confirm_requires_auth()
    {
        $response = $this->postJson('/roster/ai-document-import/confirm', [
            'import_id' => 1,
            'categories' => ['medications'],
        ]);
        $response->assertStatus(302);
    }

    public function test_list_requires_auth()
    {
        $response = $this->getJson('/roster/ai-document-import/list?client_id=' . $this->clientId);
        $response->assertStatus(302);
    }

    // ─── Upload Validation Tests ────────────────────────────

    public function test_upload_rejects_non_pdf()
    {
        $file = UploadedFile::fake()->create('malicious.php', 100, 'text/x-php');

        $response = $this->actAsUser()
            ->postJson('/roster/ai-document-import/upload', [
                'client_id' => $this->clientId,
                'file' => $file,
            ]);

        $response->assertStatus(422);
    }

    public function test_upload_rejects_oversized_file()
    {
        $file = UploadedFile::fake()->create('big.pdf', 11000, 'application/pdf');

        $response = $this->actAsUser()
            ->postJson('/roster/ai-document-import/upload', [
                'client_id' => $this->clientId,
                'file' => $file,
            ]);

        $response->assertStatus(422);
    }

    public function test_upload_rejects_missing_file()
    {
        $response = $this->actAsUser()
            ->postJson('/roster/ai-document-import/upload', [
                'client_id' => $this->clientId,
            ]);

        $response->assertStatus(422);
    }

    public function test_upload_rejects_missing_client_id()
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->actAsUser()
            ->postJson('/roster/ai-document-import/upload', [
                'file' => $file,
            ]);

        $response->assertStatus(422);
    }

    // ─── IDOR Tests ─────────────────────────────────────────

    public function test_upload_rejects_client_in_different_home()
    {
        $otherClient = DB::table('service_user')
            ->where('home_id', '!=', $this->homeId)
            ->where('is_deleted', 0)
            ->first();

        if (!$otherClient) {
            $this->markTestSkipped('No client in a different home to test IDOR.');
        }

        $file = $this->createRealPdf();

        $response = $this->actAsUser()
            ->postJson('/roster/ai-document-import/upload', [
                'client_id' => $otherClient->id,
                'file' => $file,
            ]);

        $response->assertStatus(404);
        $response->assertJson(['status' => false]);
    }

    public function test_extract_rejects_import_from_different_home()
    {
        $import = AIDocumentImport::create([
            'home_id' => 999,
            'client_id' => 1,
            'uploaded_by' => 1,
            'original_filename' => 'test.pdf',
            'stored_path' => 'imports/999/test.pdf',
            'file_size' => 1024,
            'file_mime' => 'application/pdf',
            'import_status' => 'uploaded',
        ]);

        $response = $this->actAsUser()
            ->postJson('/roster/ai-document-import/extract', [
                'import_id' => $import->id,
            ]);

        $response->assertStatus(422);
        $response->assertJson(['status' => false]);

        $import->delete();
    }

    public function test_confirm_rejects_import_from_different_home()
    {
        $import = AIDocumentImport::create([
            'home_id' => 999,
            'client_id' => 1,
            'uploaded_by' => 1,
            'original_filename' => 'test.pdf',
            'stored_path' => 'imports/999/test.pdf',
            'file_size' => 1024,
            'file_mime' => 'application/pdf',
            'import_status' => 'extracted',
            'extracted_data' => ['medications' => []],
        ]);

        $response = $this->actAsUser()
            ->postJson('/roster/ai-document-import/confirm', [
                'import_id' => $import->id,
                'categories' => ['medications'],
            ]);

        $response->assertStatus(422);
        $response->assertJson(['status' => false]);

        $import->delete();
    }

    public function test_download_rejects_import_from_different_home()
    {
        $import = AIDocumentImport::create([
            'home_id' => 999,
            'client_id' => 1,
            'uploaded_by' => 1,
            'original_filename' => 'test.pdf',
            'stored_path' => 'imports/999/test.pdf',
            'file_size' => 1024,
            'file_mime' => 'application/pdf',
            'import_status' => 'completed',
        ]);

        $response = $this->actAsUser()
            ->get('/roster/ai-document-import/download/' . $import->id);

        $response->assertStatus(404);

        $import->delete();
    }

    // ─── AI Extraction Tests (mocked) ───────────────────────

    public function test_extract_with_mocked_ai_returns_structured_data()
    {
        $import = $this->createImportRecord('uploaded');

        $mockResponse = json_encode([
            'care_history' => [],
            'medications' => [
                ['medication_name' => 'Paracetamol', 'dosage' => '500mg', 'frequency' => 'Twice daily', 'route' => 'Oral']
            ],
            'risk_assessments' => [
                ['risk_type' => 'Falls', 'risk_level' => 'high', 'description' => 'History of falls']
            ],
            'client_profile' => null,
            'body_map' => [],
            'dols' => [],
            'document_summary' => 'Care report with medication and risk data.'
        ]);

        $mockOpenAI = $this->createMock(OpenAIService::class);
        $mockOpenAI->method('isConfigured')->willReturn(true);
        $mockOpenAI->method('chatJson')->willReturn([
            'content' => $mockResponse,
            'tokens_input' => 500,
            'tokens_output' => 200,
            'model' => 'gpt-4o',
            'latency_ms' => 3000,
        ]);

        $mockTracker = $this->createMock(TokenTracker::class);
        $mockTracker->method('isCapExceeded')->willReturn(false);
        $mockTracker->method('log');

        $service = $this->getMockBuilder(AIDocumentImportService::class)
            ->setConstructorArgs([$mockOpenAI, new PIIFilter(), $mockTracker])
            ->onlyMethods(['extractTextFromFile'])
            ->getMock();

        $service->method('extractTextFromFile')->willReturn(
            'Patient Care Report. Medications: Paracetamol 500mg twice daily oral. Risk: Falls Risk High.'
        );

        $result = $service->extractDataWithAI($import->id, $this->homeId, $this->user->id);

        $this->assertTrue($result['status']);
        $this->assertNotEmpty($result['extracted_data']['medications']);
        $this->assertEquals('Paracetamol', $result['extracted_data']['medications'][0]['medication_name']);

        $import->refresh();
        $this->assertEquals('extracted', $import->import_status);

        $import->delete();
    }

    public function test_extract_rejects_when_token_cap_exceeded()
    {
        $import = $this->createImportRecord('uploaded');

        $mockOpenAI = $this->createMock(OpenAIService::class);
        $mockOpenAI->method('isConfigured')->willReturn(true);

        $mockTracker = $this->createMock(TokenTracker::class);
        $mockTracker->method('isCapExceeded')->willReturn(true);

        $service = new AIDocumentImportService($mockOpenAI, new PIIFilter(), $mockTracker);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Daily AI usage limit reached');
        $service->extractDataWithAI($import->id, $this->homeId, $this->user->id);

        $import->delete();
    }

    public function test_extract_rejects_when_ai_not_configured()
    {
        $import = $this->createImportRecord('uploaded');

        $mockOpenAI = $this->createMock(OpenAIService::class);
        $mockOpenAI->method('isConfigured')->willReturn(false);

        $mockTracker = $this->createMock(TokenTracker::class);
        $mockTracker->method('isCapExceeded')->willReturn(false);

        $service = new AIDocumentImportService($mockOpenAI, new PIIFilter(), $mockTracker);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('AI is not configured');
        $service->extractDataWithAI($import->id, $this->homeId, $this->user->id);

        $import->delete();
    }

    // ─── Import Tests ───────────────────────────────────────

    public function test_import_care_history_creates_records()
    {
        $extractedData = [
            'care_history' => [
                ['title' => 'Test History Entry', 'date' => '2025-01-01', 'description' => 'Test description for AI import.'],
            ],
            'medications' => [],
        ];

        $import = $this->createImportRecord('extracted', $extractedData);

        $mockOpenAI = $this->createMock(OpenAIService::class);
        $mockTracker = $this->createMock(TokenTracker::class);
        $service = new AIDocumentImportService($mockOpenAI, new PIIFilter(), $mockTracker);

        $countBefore = DB::table('su_care_history')
            ->where('home_id', $this->homeId)
            ->where('service_user_id', $this->clientId)
            ->count();

        $result = $service->importToDatabase($import->id, ['care_history'], $this->homeId, $this->user->id);

        $countAfter = DB::table('su_care_history')
            ->where('home_id', $this->homeId)
            ->where('service_user_id', $this->clientId)
            ->count();

        $this->assertTrue($result['status']);
        $this->assertEquals(1, $result['summary']['care_history']);
        $this->assertEquals($countBefore + 1, $countAfter);

        DB::table('su_care_history')
            ->where('home_id', $this->homeId)
            ->where('service_user_id', $this->clientId)
            ->where('title', 'Test History Entry')
            ->delete();

        $import->delete();
    }

    public function test_import_medications_creates_mar_records()
    {
        $extractedData = [
            'medications' => [
                [
                    'medication_name' => 'AI Test Paracetamol',
                    'dosage' => '500mg',
                    'dose' => '2 tablets',
                    'route' => 'Oral',
                    'frequency' => 'Twice daily',
                    'reason_for_medication' => 'Pain management test',
                ],
            ],
        ];

        $import = $this->createImportRecord('extracted', $extractedData);

        $mockOpenAI = $this->createMock(OpenAIService::class);
        $mockTracker = $this->createMock(TokenTracker::class);
        $service = new AIDocumentImportService($mockOpenAI, new PIIFilter(), $mockTracker);

        $result = $service->importToDatabase($import->id, ['medications'], $this->homeId, $this->user->id);

        $this->assertTrue($result['status']);
        $this->assertEquals(1, $result['summary']['medications']);

        $marRecord = DB::table('mar_sheets')
            ->where('home_id', $this->homeId)
            ->where('client_id', $this->clientId)
            ->where('medication_name', 'AI Test Paracetamol')
            ->first();

        $this->assertNotNull($marRecord);
        $this->assertEquals('500mg', $marRecord->dosage);
        $this->assertEquals('Oral', $marRecord->route);

        DB::table('mar_sheets')->where('id', $marRecord->id)->delete();
        $import->delete();
    }

    public function test_import_client_profile_updates_service_user()
    {
        $originalClient = DB::table('service_user')
            ->where('id', $this->clientId)
            ->where('home_id', $this->homeId)
            ->first();

        $extractedData = [
            'client_profile' => [
                'allergies' => 'AI Test Allergy - Penicillin',
                'medical_notes' => 'AI Test Medical Note',
            ],
        ];

        $import = $this->createImportRecord('extracted', $extractedData);

        $mockOpenAI = $this->createMock(OpenAIService::class);
        $mockTracker = $this->createMock(TokenTracker::class);
        $service = new AIDocumentImportService($mockOpenAI, new PIIFilter(), $mockTracker);

        $result = $service->importToDatabase($import->id, ['client_profile'], $this->homeId, $this->user->id);

        $this->assertTrue($result['status']);
        $this->assertEquals(2, $result['summary']['client_profile']);

        $updatedClient = DB::table('service_user')
            ->where('id', $this->clientId)
            ->first();

        $this->assertEquals('AI Test Allergy - Penicillin', $updatedClient->allergies);
        $this->assertEquals('AI Test Medical Note', $updatedClient->medical_notes);

        DB::table('service_user')
            ->where('id', $this->clientId)
            ->update([
                'allergies' => $originalClient->allergies,
                'medical_notes' => $originalClient->medical_notes,
            ]);

        $import->delete();
    }

    public function test_import_with_no_categories_returns_empty()
    {
        $import = $this->createImportRecord('extracted', ['medications' => []]);

        $mockOpenAI = $this->createMock(OpenAIService::class);
        $mockTracker = $this->createMock(TokenTracker::class);
        $service = new AIDocumentImportService($mockOpenAI, new PIIFilter(), $mockTracker);

        $result = $service->importToDatabase($import->id, [], $this->homeId, $this->user->id);

        $this->assertTrue($result['status']);
        $this->assertEmpty($result['summary']);

        $import->delete();
    }

    public function test_import_rejects_wrong_status()
    {
        $import = $this->createImportRecord('uploaded');

        $mockOpenAI = $this->createMock(OpenAIService::class);
        $mockTracker = $this->createMock(TokenTracker::class);
        $service = new AIDocumentImportService($mockOpenAI, new PIIFilter(), $mockTracker);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('must be extracted before importing');
        $service->importToDatabase($import->id, ['medications'], $this->homeId, $this->user->id);

        $import->delete();
    }

    // ─── Document List Tests ────────────────────────────────

    public function test_list_returns_imports_for_home()
    {
        $import = $this->createImportRecord('completed');

        $response = $this->actAsUser()
            ->getJson('/roster/ai-document-import/list?client_id=' . $this->clientId);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);
        $this->assertNotEmpty($response->json('imports'));

        $import->delete();
    }

    public function test_list_excludes_deleted_imports()
    {
        $import = AIDocumentImport::create([
            'home_id' => $this->homeId,
            'client_id' => $this->clientId,
            'uploaded_by' => $this->user->id,
            'original_filename' => 'deleted_test.pdf',
            'stored_path' => 'imports/test.pdf',
            'file_size' => 1024,
            'file_mime' => 'application/pdf',
            'import_status' => 'completed',
            'is_deleted' => 1,
        ]);

        $response = $this->actAsUser()
            ->getJson('/roster/ai-document-import/list?client_id=' . $this->clientId);

        $response->assertStatus(200);
        $filenames = collect($response->json('imports'))->pluck('filename')->toArray();
        $this->assertNotContains('deleted_test.pdf', $filenames);

        $import->forceDelete();
    }

    // ─── Delete Tests ───────────────────────────────────────

    public function test_delete_soft_deletes_import()
    {
        $import = $this->createImportRecord('completed');

        $response = $this->actAsUser()
            ->postJson('/roster/ai-document-import/delete', [
                'import_id' => $import->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $import->refresh();
        $this->assertEquals(1, $import->is_deleted);

        $import->forceDelete();
    }

    public function test_delete_rejects_import_from_different_home()
    {
        $import = AIDocumentImport::create([
            'home_id' => 999,
            'client_id' => 1,
            'uploaded_by' => 1,
            'original_filename' => 'test.pdf',
            'stored_path' => 'imports/999/test.pdf',
            'file_size' => 1024,
            'file_mime' => 'application/pdf',
            'import_status' => 'completed',
        ]);

        $response = $this->actAsUser()
            ->postJson('/roster/ai-document-import/delete', [
                'import_id' => $import->id,
            ]);

        $response->assertStatus(404);

        $import->delete();
    }

    // ─── Confirm Endpoint Validation ────────────────────────

    public function test_confirm_validates_category_names()
    {
        $response = $this->actAsUser()
            ->postJson('/roster/ai-document-import/confirm', [
                'import_id' => 1,
                'categories' => ['invalid_category', 'sql_injection'],
            ]);

        $response->assertStatus(422);
    }

    public function test_confirm_requires_categories()
    {
        $response = $this->actAsUser()
            ->postJson('/roster/ai-document-import/confirm', [
                'import_id' => 1,
            ]);

        $response->assertStatus(422);
    }

    // ─── Full Regression ────────────────────────────────────

    public function test_model_scopes_work_correctly()
    {
        $import = $this->createImportRecord('completed');

        $found = AIDocumentImport::forHome($this->homeId)
            ->forClient($this->clientId)
            ->notDeleted()
            ->where('id', $import->id)
            ->first();

        $this->assertNotNull($found);

        $notFound = AIDocumentImport::forHome(999)
            ->where('id', $import->id)
            ->first();

        $this->assertNull($notFound);

        $import->delete();
    }
}
