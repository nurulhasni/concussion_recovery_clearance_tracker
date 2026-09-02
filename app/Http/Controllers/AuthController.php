<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the demo login screen for patients and parents.
     */
    public function showLoginForm(): View
    {
        if (Patient::count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        }

        $demoPatients = Patient::all();

        return view('auth.login', compact('demoPatients'));
    }

    /**
     * Generate and dispatch a demo magic link token.
     */
    public function sendMagicLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $patient = Patient::where('parent_email', $validated['email'])->first();

        if (! $patient) {
            return back()->withErrors([
                'email' => __('app.parent_email_not_found'),
            ])->withInput();
        }

        $token = Str::random(64);
        $patient->update([
            'login_token' => $token,
            'login_token_expires_at' => now()->addMinutes(30),
        ]);

        $magicUrl = route('auth.magic_link', ['token' => $token]);

        // Log for demonstration and developer inspection
        Log::info("Demo Magic Link dispatched for Patient [{$patient->name}]: {$magicUrl}");

        return back()->with([
            'status' => __('app.magic_link_sent_message'),
            'demo_magic_link' => $magicUrl,
            'demo_patient_name' => $patient->name,
        ]);
    }

    /**
     * Verify single-use magic link token and authenticate session.
     */
    public function verifyMagicLink(string $token): RedirectResponse
    {
        $patient = Patient::where('login_token', $token)
            ->where('login_token_expires_at', '>', now())
            ->first();

        if (! $patient) {
            return redirect()->route('login')->with('error', __('app.magic_link_invalid_or_expired'));
        }

        // Consume single-use token
        $patient->update([
            'login_token' => null,
            'login_token_expires_at' => null,
        ]);

        // Establish authenticated session
        session([
            'authenticated_patient_id' => $patient->id,
            'authenticated_patient_name' => $patient->name,
        ]);

        return redirect()->route('passport.show', $patient)->with('success', __('app.auth_welcome_message', ['name' => $patient->name]));
    }

    /**
     * Clear session and log out.
     */
    public function logout(Request $request): RedirectResponse
    {
        session()->forget(['authenticated_patient_id', 'authenticated_patient_name']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', __('app.logged_out_message'));
    }
}
