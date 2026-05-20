<?php

namespace Tests\Feature;

use App\Models\FormTemplate;
use App\Models\FormSubmission;
use App\Services\AI\OpenAIService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormBuilderTest extends TestCase
{
    private int $homeId = 8;
    private $user;
    private int $clientId;
    private int $maxTemplateIdBefore;
    private int $maxSubmissionIdBefore;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = \App\User::where('user_name', 'komal')->first();

        $client = DB::table('service_user')
            ->where('home_id', $this->homeId)
            ->where('is_deleted', 0)
            ->first();
        $this->clientId = $client ? $client->id : 1;

        $this->maxTemplateIdBefore = (int) DB::table('form_templates')->max('id');
        $this->maxSubmissionIdBefore = (int) DB::table('form_submissions')->max('id');
    }

    protected function tearDown(): void
    {
        DB::table('form_submissions')->where('id', '>', $this->maxSubmissionIdBefore)->delete();
        DB::table('form_templates')->where('id', '>', $this->maxTemplateIdBefore)->delete();
        parent::tearDown();
    }

    private function actAsUser()
    {
        return $this->actingAs($this->user)->withoutMiddleware();
    }

    private function validFormJson(): string
    {
        return json_encode([
            'formTitle' => 'Test Form',
            'formDescription' => 'A test form',
            'sections' => [
                [
                    'title' => 'Section 1',
                    'fields' => [
                        ['id' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                        ['id' => 'risk', 'label' => 'Risk', 'type' => 'risk', 'required' => false, 'options' => ['Low', 'Medium', 'High']],
                    ],
                ],
            ],
        ]);
    }

    private function createTemplate(array $overrides = []): FormTemplate
    {
        return FormTemplate::create(array_merge([
            'home_id' => $this->homeId,
            'title' => 'Test Template',
            'description' => 'Test',
            'form_json' => $this->validFormJson(),
            'status' => 'published',
            'ai_generated' => 0,
            'created_by' => $this->user->id,
        ], $overrides));
    }

    private function createSubmission(int $templateId, array $overrides = []): FormSubmission
    {
        return FormSubmission::create(array_merge([
            'home_id' => $this->homeId,
            'form_template_id' => $templateId,
            'client_id' => $this->clientId,
            'form_title' => 'Test Template',
            'values_json' => json_encode(['name' => 'Test', 'risk' => 'High']),
            'submitted_by' => $this->user->id,
            'submitted_by_name' => 'Test User',
            'ai_filled' => 0,
        ], $overrides));
    }

    // ─── Page Load ─────────────────────────────────────────

    public function test_index_loads_page()
    {
        $response = $this->actAsUser()->get('/roster/form-builder');
        $response->assertStatus(200);
        $response->assertSee('Form Builder');
    }

    public function test_index_requires_auth()
    {
        $response = $this->get('/roster/form-builder');
        $response->assertRedirect();
    }

    // ─── Template CRUD ─────────────────────────────────────

    public function test_create_manual_template()
    {
        $response = $this->actAsUser()->postJson('/roster/form-builder/template', [
            'title' => 'Manual Form',
            'description' => 'Created manually',
            'form_json' => $this->validFormJson(),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $this->assertDatabaseHas('form_templates', [
            'title' => 'Manual Form',
            'home_id' => $this->homeId,
            'ai_generated' => 0,
        ]);
    }

    public function test_create_template_validates_form_json()
    {
        $response = $this->actAsUser()->postJson('/roster/form-builder/template', [
            'title' => 'Bad Form',
            'form_json' => '{"invalid": true}',
        ]);

        $response->assertStatus(422);
    }

    public function test_create_template_requires_title()
    {
        $response = $this->actAsUser()->postJson('/roster/form-builder/template', [
            'form_json' => $this->validFormJson(),
        ]);

        $response->assertStatus(422);
    }

    public function test_update_template()
    {
        $template = $this->createTemplate();

        $newJson = json_encode([
            'formTitle' => 'Updated Form',
            'formDescription' => 'Updated',
            'sections' => [
                [
                    'title' => 'Updated Section',
                    'fields' => [
                        ['id' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ],
                ],
            ],
        ]);

        $response = $this->actAsUser()->postJson('/roster/form-builder/template/' . $template->id, [
            'title' => 'Updated Form',
            'description' => 'Updated desc',
            'form_json' => $newJson,
        ]);

        $response->assertStatus(200)->assertJson(['status' => true]);
        $this->assertDatabaseHas('form_templates', ['id' => $template->id, 'title' => 'Updated Form']);
    }

    public function test_delete_template()
    {
        $template = $this->createTemplate();

        $response = $this->actAsUser()->postJson('/roster/form-builder/template/' . $template->id . '/delete');

        $response->assertStatus(200)->assertJson(['status' => true]);
        $this->assertDatabaseHas('form_templates', ['id' => $template->id, 'is_deleted' => 1]);
    }

    public function test_get_template()
    {
        $template = $this->createTemplate();

        $response = $this->actAsUser()->getJson('/roster/form-builder/template/' . $template->id);

        $response->assertStatus(200);
        $response->assertJson(['status' => true, 'template' => ['id' => $template->id, 'title' => 'Test Template']]);
    }

    // ─── IDOR Tests ─────────────────────────────────────────

    public function test_idor_template_different_home()
    {
        $template = $this->createTemplate(['home_id' => 999]);

        $response = $this->actAsUser()->getJson('/roster/form-builder/template/' . $template->id);
        $response->assertStatus(404);

        $response = $this->actAsUser()->postJson('/roster/form-builder/template/' . $template->id, [
            'title' => 'Hacked', 'form_json' => $this->validFormJson(),
        ]);
        $response->assertStatus(404);

        $response = $this->actAsUser()->postJson('/roster/form-builder/template/' . $template->id . '/delete');
        $response->assertStatus(404);

        DB::table('form_templates')->where('id', $template->id)->delete();
    }

    public function test_idor_submission_different_home()
    {
        $template = $this->createTemplate(['home_id' => 999]);
        $submission = $this->createSubmission($template->id, ['home_id' => 999]);

        $response = $this->actAsUser()->getJson('/roster/form-builder/submission/' . $submission->id);
        $response->assertStatus(404);

        $response = $this->actAsUser()->postJson('/roster/form-builder/submission/' . $submission->id, [
            'values_json' => '{"name":"hacked"}',
        ]);
        $response->assertStatus(404);

        $response = $this->actAsUser()->postJson('/roster/form-builder/submission/' . $submission->id . '/delete');
        $response->assertStatus(404);

        DB::table('form_submissions')->where('id', $submission->id)->delete();
        DB::table('form_templates')->where('id', $template->id)->delete();
    }

    // ─── Submission CRUD ─────────────────────────────────────

    public function test_save_submission_with_client()
    {
        $template = $this->createTemplate();

        $response = $this->actAsUser()->postJson('/roster/form-builder/submission', [
            'template_id' => $template->id,
            'client_id' => $this->clientId,
            'values_json' => json_encode(['name' => 'Test Client', 'risk' => 'Low']),
        ]);

        $response->assertStatus(200)->assertJson(['status' => true]);
        $this->assertDatabaseHas('form_submissions', [
            'form_template_id' => $template->id,
            'client_id' => $this->clientId,
            'home_id' => $this->homeId,
        ]);
    }

    public function test_save_submission_without_client()
    {
        $template = $this->createTemplate();

        $response = $this->actAsUser()->postJson('/roster/form-builder/submission', [
            'template_id' => $template->id,
            'values_json' => json_encode(['name' => 'General Form']),
        ]);

        $response->assertStatus(200)->assertJson(['status' => true]);
    }

    public function test_update_submission()
    {
        $template = $this->createTemplate();
        $submission = $this->createSubmission($template->id);

        $response = $this->actAsUser()->postJson('/roster/form-builder/submission/' . $submission->id, [
            'values_json' => json_encode(['name' => 'Updated', 'risk' => 'Medium']),
        ]);

        $response->assertStatus(200)->assertJson(['status' => true]);
    }

    public function test_delete_submission()
    {
        $template = $this->createTemplate();
        $submission = $this->createSubmission($template->id);

        $response = $this->actAsUser()->postJson('/roster/form-builder/submission/' . $submission->id . '/delete');

        $response->assertStatus(200)->assertJson(['status' => true]);
        $this->assertDatabaseHas('form_submissions', ['id' => $submission->id, 'is_deleted' => 1]);
    }

    public function test_get_submission()
    {
        $template = $this->createTemplate();
        $submission = $this->createSubmission($template->id);

        $response = $this->actAsUser()->getJson('/roster/form-builder/submission/' . $submission->id);

        $response->assertStatus(200);
        $response->assertJson(['status' => true, 'submission' => ['id' => $submission->id]]);
        $response->assertJsonStructure(['template' => ['id', 'title', 'form_json']]);
    }

    public function test_client_submissions()
    {
        $template = $this->createTemplate();
        $this->createSubmission($template->id);

        $response = $this->actAsUser()->getJson('/roster/form-builder/client/' . $this->clientId . '/submissions');

        $response->assertStatus(200)->assertJson(['status' => true]);
        $response->assertJsonCount(1, 'submissions');
    }

    public function test_client_submissions_different_home()
    {
        $response = $this->actAsUser()->getJson('/roster/form-builder/client/99999/submissions');
        $response->assertStatus(404);
    }

    // ─── Validation Tests ─────────────────────────────────────

    public function test_submission_invalid_values_json()
    {
        $template = $this->createTemplate();

        $response = $this->actAsUser()->postJson('/roster/form-builder/submission', [
            'template_id' => $template->id,
            'values_json' => 'not json',
        ]);

        $response->assertStatus(422);
    }

    public function test_submission_nonexistent_template()
    {
        $response = $this->actAsUser()->postJson('/roster/form-builder/submission', [
            'template_id' => 99999,
            'values_json' => json_encode(['name' => 'test']),
        ]);

        $response->assertStatus(404);
    }

    // ─── Upload Tests (mocked) ─────────────────────────────────

    public function test_upload_invalid_file_type()
    {
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $response = $this->actAsUser()->postJson('/roster/form-builder/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(422);
    }

    public function test_upload_oversized_file()
    {
        $file = UploadedFile::fake()->create('test.pdf', 11000, 'application/pdf');

        $response = $this->actAsUser()->postJson('/roster/form-builder/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(422);
    }

    public function test_upload_requires_auth()
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->post('/roster/form-builder/upload', ['file' => $file]);
        $response->assertRedirect();
    }

    // ─── AI Fill Tests (mocked) ─────────────────────────────────

    public function test_ai_fill_requires_client()
    {
        $template = $this->createTemplate();

        $response = $this->actAsUser()->postJson('/roster/form-builder/ai-fill', [
            'template_id' => $template->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_ai_fill_invalid_client()
    {
        $template = $this->createTemplate();

        $response = $this->actAsUser()->postJson('/roster/form-builder/ai-fill', [
            'template_id' => $template->id,
            'client_id' => 99999,
        ]);

        $response->assertStatus(404);
    }

    public function test_ai_fill_with_valid_client()
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => json_encode(['name' => 'Test Client', 'risk' => 'High'])]]],
                'usage' => ['prompt_tokens' => 500, 'completion_tokens' => 100],
                'model' => 'gpt-4o',
            ], 200),
        ]);

        $template = $this->createTemplate();

        $response = $this->actAsUser()->postJson('/roster/form-builder/ai-fill', [
            'template_id' => $template->id,
            'client_id' => $this->clientId,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);
        $response->assertJsonStructure(['values', 'filled_count', 'total_fields']);
    }

    // ─── Mass Assignment ─────────────────────────────────────

    public function test_mass_assignment_home_id_ignored()
    {
        $response = $this->actAsUser()->postJson('/roster/form-builder/template', [
            'title' => 'Mass Test',
            'form_json' => $this->validFormJson(),
            'home_id' => 999,
        ]);

        $response->assertStatus(200);
        $templateId = FormTemplate::where('title', 'Mass Test')->first()->id;
        $this->assertDatabaseHas('form_templates', ['id' => $templateId, 'home_id' => $this->homeId]);
    }

    // ─── Form JSON Validation ─────────────────────────────────

    public function test_form_json_validates_field_types()
    {
        $badJson = json_encode([
            'formTitle' => 'Bad',
            'sections' => [
                ['title' => 'S', 'fields' => [
                    ['id' => 'f', 'label' => 'F', 'type' => 'invalid_type'],
                ]],
            ],
        ]);

        $response = $this->actAsUser()->postJson('/roster/form-builder/template', [
            'title' => 'Bad Types',
            'form_json' => $badJson,
        ]);

        $response->assertStatus(422);
    }

    public function test_form_json_requires_options_for_select()
    {
        $badJson = json_encode([
            'formTitle' => 'Bad',
            'sections' => [
                ['title' => 'S', 'fields' => [
                    ['id' => 'f', 'label' => 'F', 'type' => 'select'],
                ]],
            ],
        ]);

        $response = $this->actAsUser()->postJson('/roster/form-builder/template', [
            'title' => 'No Options',
            'form_json' => $badJson,
        ]);

        $response->assertStatus(422);
    }

    public function test_form_json_requires_columns_for_table()
    {
        $badJson = json_encode([
            'formTitle' => 'Bad',
            'sections' => [
                ['title' => 'S', 'fields' => [
                    ['id' => 'f', 'label' => 'F', 'type' => 'table'],
                ]],
            ],
        ]);

        $response = $this->actAsUser()->postJson('/roster/form-builder/template', [
            'title' => 'No Columns',
            'form_json' => $badJson,
        ]);

        $response->assertStatus(422);
    }
}
