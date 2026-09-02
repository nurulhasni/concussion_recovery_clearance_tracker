<?php

namespace Tests\Feature;

use App\Contracts\AiCompletionProvider;
use App\Models\ApprovalLink;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock AI provider to return safe mock text during tests
        $mockProvider = $this->createMock(AiCompletionProvider::class);
        $mockProvider->method('complete')->willReturn('Observed symptoms demonstrate steady recovery progression.');
        $this->app->instance(AiCompletionProvider::class, $mockProvider);
    }

    public function test_approver_can_view_approval_portal_with_full_context(): void
    {
        $patient = Patient::create([
            'name' => 'Test Athlete',
            'injury_type' => 'Collision',
            'injury_date' => now()->subDays(6)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'test@example.com',
        ]);

        $link = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 2,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Smith',
            'token' => 'test-token-valid-portal-12345',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        $response = $this->get("/approve/{$link->token}");

        $response->assertStatus(200);
        $response->assertSee('Test Athlete');
        $response->assertSee('Dr. Smith');
        $response->assertSee('Submit Clearance Decision');
    }

    public function test_get_approve_token_returns_404_when_token_is_used_replay_prevention(): void
    {
        $patient = Patient::create([
            'name' => 'Test Athlete',
            'injury_type' => 'Collision',
            'injury_date' => now()->subDays(6)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'test@example.com',
        ]);

        $link = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 2,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Smith',
            'token' => 'used-token-replay-test-555',
            'expires_at' => now()->addDays(5),
            'is_used' => true, // Already consumed!
        ]);

        // Replay attempt must return 404 Not Found
        $response = $this->get("/approve/{$link->token}");
        $response->assertStatus(404);
    }

    public function test_get_approve_token_returns_404_when_token_expired_or_invalid(): void
    {
        $patient = Patient::create([
            'name' => 'Test Athlete',
            'injury_type' => 'Collision',
            'injury_date' => now()->subDays(6)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'test@example.com',
        ]);

        $link = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 2,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Smith',
            'token' => 'expired-token-777',
            'expires_at' => now()->subDay(), // Expired
            'is_used' => false,
        ]);

        $response = $this->get("/approve/{$link->token}");
        $response->assertStatus(404);

        $responseInvalid = $this->get('/approve/non-existent-token-xyz');
        $responseInvalid->assertStatus(404);
    }

    public function test_stage_4_approval_blocked_with_403_if_activity_step_less_than_6(): void
    {
        $patient = Patient::create([
            'name' => 'Test Athlete',
            'injury_type' => 'Collision',
            'injury_date' => now()->subDays(12)->toDateString(),
            'current_stage' => 3,
            'activity_step' => 4, // Only at Step 4 (requires Step 6!)
            'parent_email' => 'test@example.com',
        ]);

        $link = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 4,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Smith',
            'token' => 'stage4-gate-test-token-123',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        // Attempting to access Final Clearance before reaching Step 6 returns 403 Forbidden
        $response = $this->get("/approve/{$link->token}");
        $response->assertStatus(403);
    }

    public function test_approver_decision_records_and_consumes_token(): void
    {
        $patient = Patient::create([
            'name' => 'Test Athlete',
            'injury_type' => 'Collision',
            'injury_date' => now()->subDays(6)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'test@example.com',
        ]);

        $link = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 2,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Smith',
            'token' => 'decision-consume-token-888',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        $response = $this->post("/approve/{$link->token}", [
            'decision' => 'approved',
            'comments' => 'Patient has completed baseline cognitive rest.',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Decision Recorded Successfully');

        // Token must be marked as used
        $link->refresh();
        $this->assertTrue($link->is_used);

        // Record must be stored with comments and AI recommendation
        $this->assertDatabaseHas('approval_records', [
            'patient_id' => $patient->id,
            'stage' => 2,
            'approver_role' => 'doctor',
            'decision' => 'approved',
            'comments' => 'Patient has completed baseline cognitive rest.',
            'ai_recommendation' => 'Observed symptoms demonstrate steady recovery progression.',
        ]);
    }

    public function test_stage_does_not_advance_when_only_some_approvers_have_approved_multi_party_consensus(): void
    {
        $patient = Patient::create([
            'name' => 'Multi Party Patient',
            'injury_type' => 'Collision',
            'injury_date' => now()->subDays(10)->toDateString(),
            'current_stage' => 2,
            'parent_email' => 'test@example.com',
        ]);

        // Stage 3 requires BOTH Doctor and School sign-offs
        $doctorLink = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 3,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Smith',
            'token' => 'consensus-doctor-token-111',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        $schoolLink = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 3,
            'approver_role' => 'school',
            'approver_name' => 'Nurse Kelly',
            'token' => 'consensus-school-token-222',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        // Doctor approves...
        $this->post("/approve/{$doctorLink->token}", [
            'decision' => 'approved',
        ]);

        $patient->refresh();
        // Crucial Check: Stage MUST NOT advance yet because School has not decided!
        $this->assertEquals(2, $patient->current_stage);
    }

    public function test_stage_advances_only_when_all_required_approvers_for_stage_have_approved(): void
    {
        $patient = Patient::create([
            'name' => 'Advancing Patient',
            'injury_type' => 'Collision',
            'injury_date' => now()->subDays(10)->toDateString(),
            'current_stage' => 2,
            'parent_email' => 'test@example.com',
        ]);

        $doctorLink = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 3,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Smith',
            'token' => 'adv-doctor-token-111',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        $schoolLink = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 3,
            'approver_role' => 'school',
            'approver_name' => 'Nurse Kelly',
            'token' => 'adv-school-token-222',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        // 1. Doctor approves
        $this->post("/approve/{$doctorLink->token}", ['decision' => 'approved']);
        $patient->refresh();
        $this->assertEquals(2, $patient->current_stage);

        // 2. School approves -> Now all 2/2 approved!
        $this->post("/approve/{$schoolLink->token}", ['decision' => 'approved']);
        $patient->refresh();

        // Stage advances to Milestone 3 and initializes activity step to 1
        $this->assertEquals(3, $patient->current_stage);
        $this->assertEquals(1, $patient->activity_step);
    }

    public function test_stage_does_not_advance_if_any_approver_rejects(): void
    {
        $patient = Patient::create([
            'name' => 'Rejected Patient',
            'injury_type' => 'Collision',
            'injury_date' => now()->subDays(10)->toDateString(),
            'current_stage' => 2,
            'parent_email' => 'test@example.com',
        ]);

        $doctorLink = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 3,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Smith',
            'token' => 'rej-doctor-token-111',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        $schoolLink = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 3,
            'approver_role' => 'school',
            'approver_name' => 'Nurse Kelly',
            'token' => 'rej-school-token-222',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        // Doctor approves, but School rejects due to symptoms in class
        $this->post("/approve/{$doctorLink->token}", ['decision' => 'approved']);
        $this->post("/approve/{$schoolLink->token}", [
            'decision' => 'rejected',
            'comments' => 'Student still had headache in math class.',
        ]);

        $patient->refresh();
        // Stage MUST remain at 2
        $this->assertEquals(2, $patient->current_stage);
    }
}
