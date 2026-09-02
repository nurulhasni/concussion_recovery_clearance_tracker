<?php

namespace Tests\Feature;

use App\Contracts\AiCompletionProvider;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SymptomReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_mild_symptom_in_stage_3_increments_activity_step(): void
    {
        $patient = Patient::create([
            'name' => 'Maya Chen',
            'injury_type' => 'Soccer collision',
            'injury_date' => now()->subDays(10)->toDateString(),
            'current_stage' => 3,
            'activity_step' => 2,
            'parent_email' => 'maya@example.com',
        ]);

        // Mock AI to return mild symptom with no red flag
        $mockProvider = $this->createMock(AiCompletionProvider::class);
        $mockProvider->method('complete')->willReturn(json_encode([
            'severity' => 'mild',
            'red_flag' => false,
            'reasoning' => 'No symptoms during light exercise.',
            'extracted_symptoms' => ['none'],
        ]));
        $this->app->instance(AiCompletionProvider::class, $mockProvider);

        $response = $this->withSession([
            'authenticated_patient_id' => $patient->id,
        ])->post('/symptom-reports', [
            'patient_id' => $patient->id,
            'report_text' => 'Felt great during exercise, no headache or dizziness.',
        ]);

        $response->assertRedirect(route('passport.show', $patient));

        $patient->refresh();
        // Activity step advanced from 2 to 3
        $this->assertEquals(3, $patient->activity_step);
        $this->assertEquals(3, $patient->current_stage);

        $this->assertDatabaseHas('symptom_reports', [
            'patient_id' => $patient->id,
            'ai_severity' => 'mild',
            'ai_red_flag' => false,
        ]);
    }

    public function test_red_flag_symptom_in_stage_3_decrements_activity_step(): void
    {
        $patient = Patient::create([
            'name' => 'Maya Chen',
            'injury_type' => 'Soccer collision',
            'injury_date' => now()->subDays(10)->toDateString(),
            'current_stage' => 3,
            'activity_step' => 4,
            'parent_email' => 'maya@example.com',
        ]);

        // Mock AI to return severe red flag
        $mockProvider = $this->createMock(AiCompletionProvider::class);
        $mockProvider->method('complete')->willReturn(json_encode([
            'severity' => 'severe',
            'red_flag' => true,
            'reasoning' => 'Dizziness and worsening headache during drills.',
            'extracted_symptoms' => ['dizziness', 'worsening headache'],
        ]));
        $this->app->instance(AiCompletionProvider::class, $mockProvider);

        $response = $this->withSession([
            'authenticated_patient_id' => $patient->id,
        ])->post('/symptom-reports', [
            'patient_id' => $patient->id,
            'report_text' => 'Severe headache came back during practice and felt dizzy.',
        ]);

        $patient->refresh();
        // Step decreases by 1 (4 -> 3)
        $this->assertEquals(3, $patient->activity_step);
        $this->assertEquals(3, $patient->current_stage);
    }

    public function test_red_flag_symptom_at_step_1_downgrades_milestone_to_stage_2(): void
    {
        $patient = Patient::create([
            'name' => 'Maya Chen',
            'injury_type' => 'Soccer collision',
            'injury_date' => now()->subDays(10)->toDateString(),
            'current_stage' => 3,
            'activity_step' => 1, // Already at minimum step 1
            'parent_email' => 'maya@example.com',
        ]);

        // Mock AI to return red flag
        $mockProvider = $this->createMock(AiCompletionProvider::class);
        $mockProvider->method('complete')->willReturn(json_encode([
            'severity' => 'severe',
            'red_flag' => true,
            'reasoning' => 'Severe headache and vomiting.',
            'extracted_symptoms' => ['repeated vomiting'],
        ]));
        $this->app->instance(AiCompletionProvider::class, $mockProvider);

        $response = $this->withSession([
            'authenticated_patient_id' => $patient->id,
        ])->post('/symptom-reports', [
            'patient_id' => $patient->id,
            'report_text' => 'Repeated vomiting this morning and severe headache.',
        ]);

        $patient->refresh();
        // Milestone downgraded from Stage 3 to Stage 2 (School Recovery) and activity_step cleared
        $this->assertEquals(2, $patient->current_stage);
        $this->assertNull($patient->activity_step);
    }

    public function test_red_flag_in_stage_2_downgrades_to_stage_1(): void
    {
        $patient = Patient::create([
            'name' => 'Jordan Taylor',
            'injury_type' => 'Hurdle fall',
            'injury_date' => now()->subDays(8)->toDateString(),
            'current_stage' => 2,
            'parent_email' => 'jordan@example.com',
        ]);

        $mockProvider = $this->createMock(AiCompletionProvider::class);
        $mockProvider->method('complete')->willReturn(json_encode([
            'severity' => 'severe',
            'red_flag' => true,
            'reasoning' => 'Extreme sensitivity and confusion in class.',
            'extracted_symptoms' => ['confusion'],
        ]));
        $this->app->instance(AiCompletionProvider::class, $mockProvider);

        $response = $this->withSession([
            'authenticated_patient_id' => $patient->id,
        ])->post('/symptom-reports', [
            'patient_id' => $patient->id,
            'report_text' => 'Severe confusion in class today.',
        ]);

        $patient->refresh();
        // Milestone downgraded from 2 to 1 (Initial Assessment / Complete Rest)
        $this->assertEquals(1, $patient->current_stage);
    }
}
