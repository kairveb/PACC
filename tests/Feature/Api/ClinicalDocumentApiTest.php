<?php

namespace Tests\Feature\Api;

use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\HimsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClinicalDocumentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HimsSeeder::class);
    }

    public function test_registration_user_can_upload_and_list_clinical_documents(): void
    {
        Storage::fake('local');

        $user = User::where('email', 'registration@coor.test')->first();
        $patient = Patient::first();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/patients/{$patient->id}/clinical-documents", [
            'name' => 'Lab Report',
            'document' => UploadedFile::fake()->create('report.pdf', 100),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Lab Report')
            ->assertJsonPath('data.patient_id', $patient->id);

        $this->assertDatabaseHas('clinical_documents', ['patient_id' => $patient->id, 'name' => 'Lab Report']);

        $list = $this->getJson("/api/v1/patients/{$patient->id}/clinical-documents");
        $list->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_patient_cannot_upload_clinical_document_for_other_patient(): void
    {
        $patient = Patient::first();
        $user = User::where('email', 'patient@coor.test')->first();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/patients/{$patient->id}/clinical-documents", [
            'document' => UploadedFile::fake()->create('report.pdf', 100),
        ]);

        $response->assertStatus(403);
    }

    public function test_patient_phone_with_letters_is_rejected(): void
    {
        $user = User::where('email', 'registration@coor.test')->firstOrFail();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/patients', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'date_of_birth' => '1995-03-04',
            'sex' => 'Female',
            'phone' => '09ABCD12345',
            'email' => 'jane.doe@example.test',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }
}
