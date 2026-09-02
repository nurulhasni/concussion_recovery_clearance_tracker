<?php

namespace Tests\Feature;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Patient & Parent Access');
    }

    public function test_parent_can_request_demo_magic_link(): void
    {
        $patient = Patient::create([
            'name' => 'Demo Student',
            'injury_type' => 'Sports collision',
            'injury_date' => now()->subDays(5)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'parent@example.com',
        ]);

        $response = $this->post('/login/magic-link', [
            'email' => 'parent@example.com',
        ]);

        $response->assertSessionHas('demo_magic_link');
        $patient->refresh();
        $this->assertNotNull($patient->login_token);
        $this->assertNotNull($patient->login_token_expires_at);
    }

    public function test_invalid_parent_email_returns_error(): void
    {
        $response = $this->post('/login/magic-link', [
            'email' => 'unknown@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_valid_magic_link_token_authenticates_session_and_redirects_to_passport(): void
    {
        $patient = Patient::create([
            'name' => 'Demo Student',
            'injury_type' => 'Sports collision',
            'injury_date' => now()->subDays(5)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'parent@example.com',
            'login_token' => 'valid-test-token-123456789012345678901234567890123456789012345678',
            'login_token_expires_at' => now()->addMinutes(30),
        ]);

        $response = $this->get('/auth/magic-link/'.$patient->login_token);

        $response->assertRedirect(route('passport.show', $patient));
        $this->assertEquals($patient->id, session('authenticated_patient_id'));

        // Single-use token must be consumed/cleared
        $patient->refresh();
        $this->assertNull($patient->login_token);
    }

    public function test_invalid_or_expired_magic_link_token_is_rejected(): void
    {
        $patient = Patient::create([
            'name' => 'Expired Student',
            'injury_type' => 'Sports collision',
            'injury_date' => now()->subDays(5)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'expired@example.com',
            'login_token' => 'expired-token-123',
            'login_token_expires_at' => now()->subMinute(),
        ]);

        $response = $this->get('/auth/magic-link/expired-token-123');

        $response->assertRedirect('/login');
        $this->assertNull(session('authenticated_patient_id'));
    }

    public function test_unauthenticated_user_cannot_view_passport(): void
    {
        $patient = Patient::create([
            'name' => 'Demo Student',
            'injury_type' => 'Sports collision',
            'injury_date' => now()->subDays(5)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'parent@example.com',
        ]);

        $response = $this->get("/patients/{$patient->id}/passport");

        $response->assertRedirect('/login');
    }

    public function test_authenticated_patient_a_cannot_view_patient_b_passport_cross_patient_isolation(): void
    {
        $patientA = Patient::create([
            'name' => 'Patient Alice',
            'injury_type' => 'Sports collision',
            'injury_date' => now()->subDays(5)->toDateString(),
            'current_stage' => 1,
            'parent_email' => 'alice.parent@example.com',
        ]);

        $patientB = Patient::create([
            'name' => 'Patient Bob',
            'injury_type' => 'Fall',
            'injury_date' => now()->subDays(3)->toDateString(),
            'current_stage' => 2,
            'parent_email' => 'bob.parent@example.com',
        ]);

        // Authenticated as Patient A
        $response = $this->withSession([
            'authenticated_patient_id' => $patientA->id,
            'authenticated_patient_name' => $patientA->name,
        ])->get("/patients/{$patientB->id}/passport");

        // Strict 403 Forbidden: Zero data leak of Patient B's records
        $response->assertStatus(403);
    }

    public function test_logout_clears_session(): void
    {
        $response = $this->withSession([
            'authenticated_patient_id' => (string) Str::uuid(),
        ])->get('/logout');

        $response->assertRedirect('/login');
        $this->assertNull(session('authenticated_patient_id'));
    }
}
