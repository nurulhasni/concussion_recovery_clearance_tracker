<?php

namespace Tests\Feature;

use App\Contracts\AiCompletionProvider;
use App\Models\ApprovalLink;
use App\Models\Patient;
use App\Models\SymptomReport;
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

    public function test_red_flag_auto_downgrade_generates_approval_links_and_deduplicates_on_repeated_reports(): void
    {
        $patient = Patient::create([
            'name' => 'Jordan Taylor',
            'injury_type' => 'Hurdle fall',
            'injury_date' => now()->subDays(8)->toDateString(),
            'current_stage' => 2,
            'parent_email' => 'jordan@example.com',
        ]);

        // Prior used link from when patient originally advanced to stage 2
        ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 2,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Elizabeth Blackwell',
            'token' => 'prior-used-stage2-token-1234567890123456789012345678901234567890',
            'expires_at' => now()->subDays(2),
            'is_used' => true,
        ]);

        $mockProvider = $this->createMock(AiCompletionProvider::class);
        $mockProvider->method('complete')->willReturn(json_encode([
            'severity' => 'severe',
            'red_flag' => true,
            'reasoning' => 'Severe recurrent headache and confusion.',
            'extracted_symptoms' => ['severe headache', 'confusion'],
        ]));
        $this->app->instance(AiCompletionProvider::class, $mockProvider);

        // 1st red flag report -> triggers auto-downgrade from Stage 2 to Stage 1
        $this->withSession([
            'authenticated_patient_id' => $patient->id,
        ])->post('/symptom-reports', [
            'patient_id' => $patient->id,
            'report_text' => 'Severe headache and confusion returned.',
        ]);

        $patient->refresh();
        $this->assertEquals(1, $patient->current_stage);

        // Assert fresh link for stage 2 has been generated with role doctor
        $newStage2Links = ApprovalLink::where('patient_id', $patient->id)
            ->where('for_stage', 2)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->get();

        $this->assertCount(1, $newStage2Links);
        $firstNewLink = $newStage2Links->first();
        $this->assertEquals('doctor', $firstNewLink->approver_role);
        $this->assertEquals('Dr. Elizabeth Blackwell', $firstNewLink->approver_name);
        $this->assertEquals(64, strlen($firstNewLink->token));

        // 2nd red flag report while still at Stage 1 -> should NOT generate duplicate links
        $this->withSession([
            'authenticated_patient_id' => $patient->id,
        ])->post('/symptom-reports', [
            'patient_id' => $patient->id,
            'report_text' => 'Still experiencing headache and sensitivity.',
        ]);

        $newStage2LinksAfterSecondReport = ApprovalLink::where('patient_id', $patient->id)
            ->where('for_stage', 2)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->get();

        // Count must strictly remain 1 (no duplicate accumulated)
        $this->assertCount(1, $newStage2LinksAfterSecondReport);
        $this->assertEquals($firstNewLink->id, $newStage2LinksAfterSecondReport->first()->id);
    }

    public function test_red_flag_auto_downgrade_regenerates_links_if_existing_unused_link_is_expired(): void
    {
        $patient = Patient::create([
            'name' => 'Expired Link Athlete',
            'injury_type' => 'Fall',
            'injury_date' => now()->subDays(10)->toDateString(),
            'current_stage' => 2,
            'parent_email' => 'athlete@example.com',
        ]);

        // Existing unused link that has EXPIRED
        $expiredLink = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 2,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Existing',
            'token' => 'expired-stage2-token-12345678901234567890123456789012345678901234',
            'expires_at' => now()->subDays(1),
            'is_used' => false,
        ]);

        $mockProvider = $this->createMock(AiCompletionProvider::class);
        $mockProvider->method('complete')->willReturn(json_encode([
            'severity' => 'severe',
            'red_flag' => true,
            'reasoning' => 'Severe dizziness.',
            'extracted_symptoms' => ['dizziness'],
        ]));
        $this->app->instance(AiCompletionProvider::class, $mockProvider);

        $this->withSession([
            'authenticated_patient_id' => $patient->id,
        ])->post('/symptom-reports', [
            'patient_id' => $patient->id,
            'report_text' => 'Severe dizziness experienced today.',
        ]);

        $patient->refresh();
        $this->assertEquals(1, $patient->current_stage);

        // A new active link should have been created to replace the expired one
        $activeLinks = ApprovalLink::where('patient_id', $patient->id)
            ->where('for_stage', 2)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->get();

        $this->assertCount(1, $activeLinks);
        $this->assertNotEquals($expiredLink->id, $activeLinks->first()->id);
        $this->assertEquals('Dr. Existing', $activeLinks->first()->approver_name);
    }

    public function test_passport_displays_up_to_3_recent_symptom_reports(): void
    {
        $patient = Patient::create([
            'name' => 'History Athlete',
            'injury_type' => 'Practice fall',
            'injury_date' => now()->subDays(10)->toDateString(),
            'current_stage' => 2,
            'parent_email' => 'history@example.com',
        ]);

        for ($i = 1; $i <= 8; $i++) {
            SymptomReport::create([
                'patient_id' => $patient->id,
                'report_text' => "Unique Report Content {$i}",
                'ai_severity' => 'mild',
                'ai_red_flag' => false,
                'reported_at' => now()->subDays(9 - $i),
            ]);
        }

        $response = $this->withSession([
            'authenticated_patient_id' => $patient->id,
        ])->get(route('passport.show', $patient));

        $response->assertStatus(200);
        // Should show the 3 most recent entries (8, 7, 6)
        $response->assertSee('Unique Report Content 8');
        $response->assertSee('Unique Report Content 7');
        $response->assertSee('Unique Report Content 6');
        // Report 1 should not appear in the top 3 list or 7-day grid
        $response->assertDontSee('Unique Report Content 1');
    }
}
