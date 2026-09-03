<?php

namespace Tests\Feature;

use App\Contracts\AiCompletionProvider;
use App\Models\ApprovalLink;
use App\Models\Patient;
use App\Traits\BuildsRecoveryContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalFlowTest extends TestCase
{
    use BuildsRecoveryContext, RefreshDatabase;

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
        $response->assertSee('Submit Decision');
        $response->assertDontSee(route('login'));
        $response->assertDontSee('checked');
    }

    public function test_approval_portal_in_indonesian_renders_submit_decision_and_hides_login(): void
    {
        $patient = Patient::create([
            'name' => 'Indonesian Athlete',
            'injury_type' => 'Collision',
            'injury_date' => now()->subDays(4)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'id@example.com',
        ]);

        $link = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 2,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Budi',
            'token' => 'id-test-token-portal-999',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        $response = $this->withSession(['locale' => 'id'])->get("/approve/{$link->token}");

        $response->assertStatus(200);
        $response->assertSee('Kirim Keputusan');
        $response->assertDontSee(route('login'));
        $response->assertDontSee('checked');
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

        // Record must be stored with comments, AI recommendation, and approval_link_id
        $this->assertDatabaseHas('approval_records', [
            'approval_link_id' => $link->id,
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

    public function test_milestone_advance_generates_approval_links_for_next_stage_with_consistent_approver_names(): void
    {
        $patient = Patient::create([
            'name' => 'Progression Athlete',
            'injury_type' => 'Contact during hockey match',
            'injury_date' => now()->subDays(5)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'parent@example.com',
        ]);

        $stage2DoctorLink = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 2,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Gregory House, MD',
            'token' => 'stage2-doctor-token-unique-test-1234567890123456789012345678901234',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        // Approve stage 2 milestone
        $response = $this->post("/approve/{$stage2DoctorLink->token}", [
            'decision' => 'approved',
            'comments' => 'Cleared for school recovery stage.',
        ]);

        $response->assertStatus(200);

        $patient->refresh();
        $this->assertEquals(2, $patient->current_stage);

        // Required roles for Stage 3 from config
        $expectedRoles = config('milestone_approvers.3');
        $this->assertEquals(['doctor', 'school'], $expectedRoles);

        $stage3Links = ApprovalLink::where('patient_id', $patient->id)
            ->where('for_stage', 3)
            ->get();

        $this->assertCount(count($expectedRoles), $stage3Links);

        // Doctor link should reuse Dr. Gregory House
        $doctorLink = $stage3Links->firstWhere('approver_role', 'doctor');
        $this->assertNotNull($doctorLink);
        $this->assertEquals('Dr. Gregory House, MD', $doctorLink->approver_name);
        $this->assertEquals(64, strlen($doctorLink->token));
        $this->assertFalse($doctorLink->is_used);
        $this->assertTrue($doctorLink->expires_at->isFuture());

        // School link should use fallback since no previous school link existed
        $schoolLink = $stage3Links->firstWhere('approver_role', 'school');
        $this->assertNotNull($schoolLink);
        $this->assertNotEmpty($schoolLink->approver_name);
        $this->assertEquals('Assigned School Staff', $schoolLink->approver_name);
        $this->assertEquals(64, strlen($schoolLink->token));
        $this->assertFalse($schoolLink->is_used);
        $this->assertTrue($schoolLink->expires_at->isFuture());

        // Tokens must be distinct
        $this->assertNotEquals($doctorLink->token, $schoolLink->token);
    }

    public function test_advance_does_not_duplicate_existing_active_unused_links_but_regenerates_if_expired(): void
    {
        $patient = Patient::create([
            'name' => 'Dedup Patient',
            'injury_type' => 'Practice fall',
            'injury_date' => now()->subDays(7)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'parent@example.com',
        ]);

        $stage2DoctorLink = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 2,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Wilson',
            'token' => 'active-stage2-token-unique-test-12345678901234567890123456789012',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        // Pre-create an active doctor link for stage 3
        $preExistingDoctorLink = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 3,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Wilson',
            'token' => 'pre-existing-active-doctor-stage3-token-1234567890123456789012345',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        // Pre-create an EXPIRED school link for stage 3 (should be regenerated)
        $expiredSchoolLink = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 3,
            'approver_role' => 'school',
            'approver_name' => 'Old School Staff',
            'token' => 'expired-school-stage3-token-123456789012345678901234567890123456',
            'expires_at' => now()->subDay(),
            'is_used' => false,
        ]);

        // Approve stage 2
        $this->post("/approve/{$stage2DoctorLink->token}", ['decision' => 'approved']);

        $patient->refresh();
        $this->assertEquals(2, $patient->current_stage);

        // Doctor link for stage 3 should not have been duplicated
        $stage3DoctorLinks = ApprovalLink::where('patient_id', $patient->id)
            ->where('for_stage', 3)
            ->where('approver_role', 'doctor')
            ->where('is_used', false)
            ->get();
        $this->assertCount(1, $stage3DoctorLinks);
        $this->assertEquals($preExistingDoctorLink->id, $stage3DoctorLinks->first()->id);

        // School link should have a new active link generated because the old one was expired
        $activeStage3SchoolLinks = ApprovalLink::where('patient_id', $patient->id)
            ->where('for_stage', 3)
            ->where('approver_role', 'school')
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->get();
        $this->assertCount(1, $activeStage3SchoolLinks);
        $this->assertNotEquals($expiredSchoolLink->id, $activeStage3SchoolLinks->first()->id);
        // And it reused the approver_name 'Old School Staff'
        $this->assertEquals('Old School Staff', $activeStage3SchoolLinks->first()->approver_name);
    }

    public function test_downgraded_patient_new_link_shows_pending_and_is_not_falsely_satisfied_by_old_record(): void
    {
        // 1. Athlete at stage 1
        $patient = Patient::create([
            'name' => 'Cycle Athlete',
            'injury_type' => 'Impact',
            'injury_date' => now()->subDays(6)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'cycle@example.com',
        ]);

        $initialDoctorLink = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 2,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Cycle Doctor',
            'token' => 'cycle-doctor-stage2-token-initial-1234567890123456789012345678901234',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        // (a) Approve patient from stage 1 to stage 2
        $this->post("/approve/{$initialDoctorLink->token}", [
            'decision' => 'approved',
            'comments' => 'Initial stage 2 clearance granted.',
        ]);

        $patient->refresh();
        $this->assertEquals(2, $patient->current_stage);

        // Verify old ApprovalRecord exists with approval_link_id
        $this->assertDatabaseHas('approval_records', [
            'patient_id' => $patient->id,
            'stage' => 2,
            'approver_role' => 'doctor',
            'decision' => 'approved',
            'approval_link_id' => $initialDoctorLink->id,
        ]);

        // (b) Simulate a red-flag-triggered downgrade back to stage 1
        $mockProvider = $this->createMock(AiCompletionProvider::class);
        $mockProvider->method('complete')->willReturn(json_encode([
            'severity' => 'severe',
            'red_flag' => true,
            'reasoning' => 'Severe recurrent headache and confusion.',
            'extracted_symptoms' => ['severe headache', 'confusion'],
        ]));
        $this->app->instance(AiCompletionProvider::class, $mockProvider);

        $this->withSession([
            'authenticated_patient_id' => $patient->id,
        ])->post('/symptom-reports', [
            'patient_id' => $patient->id,
            'report_text' => 'Severe headache and confusion returned today.',
        ]);

        $patient->refresh();
        $this->assertEquals(1, $patient->current_stage);

        // (c) Verify a NEW ApprovalLink for stage 2/doctor exists and is_used=false
        $newDoctorLink = ApprovalLink::where('patient_id', $patient->id)
            ->where('for_stage', 2)
            ->where('approver_role', 'doctor')
            ->where('is_used', false)
            ->first();

        $this->assertNotNull($newDoctorLink);
        $this->assertNotEquals($initialDoctorLink->id, $newDoctorLink->id);
        $this->assertFalse($newDoctorLink->is_used);

        // (d) Verify that when viewing this patient's context (buildContext), the doctor's approval status for stage 2 shows 'pending', NOT 'approved'
        $context = $this->buildContext($patient);
        $approvalStatuses = collect($context['approval_statuses']);
        $doctorStatus = $approvalStatuses->firstWhere('role', 'doctor');

        $this->assertNotNull($doctorStatus);
        $this->assertEquals('pending', $doctorStatus['status']);
        $this->assertNotEquals('approved', $doctorStatus['status']);
        $this->assertEquals($newDoctorLink->token, $doctorStatus['token']);
        $this->assertFalse($context['all_approvals_ready']);
    }

    public function test_rejection_requires_comments_with_custom_message(): void
    {
        $patient = Patient::create([
            'name' => 'Validation Test Patient',
            'injury_type' => 'Practice fall',
            'injury_date' => now()->subDays(4)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'valid@example.com',
        ]);

        $link = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 2,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Reviewer',
            'token' => 'validation-test-reject-token-1234567890123456789012345678901234',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        // Rejection without comments must fail validation
        $response = $this->from("/approve/{$link->token}")
            ->post("/approve/{$link->token}", [
                'decision' => 'rejected',
                'comments' => '',
            ]);

        $response->assertRedirect("/approve/{$link->token}");
        $response->assertSessionHasErrors([
            'comments' => "Please explain why this milestone is being held, so the patient/parent and other approvers understand what's needed next.",
        ]);

        $this->assertDatabaseMissing('approval_records', [
            'patient_id' => $patient->id,
            'decision' => 'rejected',
        ]);
        $link->refresh();
        $this->assertFalse($link->is_used);

        // Rejection with comments must succeed
        $successResponse = $this->post("/approve/{$link->token}", [
            'decision' => 'rejected',
            'comments' => 'Still experiencing dizziness under light exertion.',
        ]);

        $successResponse->assertStatus(200);
        $this->assertDatabaseHas('approval_records', [
            'patient_id' => $patient->id,
            'decision' => 'rejected',
            'comments' => 'Still experiencing dizziness under light exertion.',
        ]);
        $link->refresh();
        $this->assertTrue($link->is_used);
    }

    public function test_approval_decision_does_not_require_comments(): void
    {
        $patient = Patient::create([
            'name' => 'Approval Optional Comments Patient',
            'injury_type' => 'Soccer collision',
            'injury_date' => now()->subDays(7)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'soccer@example.com',
        ]);

        $link = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 2,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Optional',
            'token' => 'optional-comments-token-123456789012345678901234567890123456',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        // Approval with empty comments must succeed without validation errors
        $response = $this->post("/approve/{$link->token}", [
            'decision' => 'approved',
            'comments' => '',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('approval_records', [
            'patient_id' => $patient->id,
            'decision' => 'approved',
            'comments' => null,
        ]);
        $link->refresh();
        $this->assertTrue($link->is_used);
    }

    public function test_rejection_comments_are_displayed_in_confirmed_view_and_recovery_passport(): void
    {
        $patient = Patient::create([
            'name' => 'Notes Display Patient',
            'injury_type' => 'Practice fall',
            'injury_date' => now()->subDays(3)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'notes@example.com',
        ]);

        $link = ApprovalLink::create([
            'patient_id' => $patient->id,
            'for_stage' => 2,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Neurologist',
            'token' => 'rejection-notes-token-123456789012345678901234567890123456',
            'expires_at' => now()->addDays(5),
            'is_used' => false,
        ]);

        $clinicalNote = 'Patient exhibits persistent photophobia. Hold at Milestone 1 for 48 more hours.';

        // 1. Submit rejection with clinical note
        $response = $this->post("/approve/{$link->token}", [
            'decision' => 'rejected',
            'comments' => $clinicalNote,
        ]);

        $response->assertStatus(200);
        // Confirmed view must display the submitted clinical note
        $response->assertSee($clinicalNote);
        $response->assertSee('holding at current stage based on clinical review');

        // 2. Trait context must include comments
        $context = $this->buildContext($patient);
        $doctorStatus = collect($context['approval_statuses'])->firstWhere('role', 'doctor');
        $this->assertEquals($clinicalNote, $doctorStatus['comments']);

        // 3. Recovery Passport dashboard must display the clinical note
        $passportResponse = $this->withSession([
            'authenticated_patient_id' => $patient->id,
            'authenticated_patient_name' => $patient->name,
        ])->get(route('passport.show', $patient));

        $passportResponse->assertStatus(200);
        $passportResponse->assertSee($clinicalNote);
        $passportResponse->assertSee('Dr. Neurologist');
    }
}
