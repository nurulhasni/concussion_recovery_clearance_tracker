<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PassportController;
use App\Http\Controllers\SymptomReportController;
use App\Models\Patient;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', function () {
    if ($patientId = session('authenticated_patient_id')) {
        $patient = Patient::find($patientId);
        if ($patient) {
            return redirect()->route('passport.show', $patient);
        }
    }

    return redirect()->route('login');
});

// Localization switch route
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'id'], true)) {
        session(['locale' => $locale]);
    }

    return back();
})->name('lang.switch');

// Authentication & Demo Magic Link routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login/magic-link', [AuthController::class, 'sendMagicLink'])->name('auth.magic_link.send');
Route::get('/auth/magic-link/{token}', [AuthController::class, 'verifyMagicLink'])->name('auth.magic_link');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Patient Recovery Passport & Symptom Logging (Guarded by EnsurePatientAuthenticated)
Route::middleware('auth.patient')->group(function () {
    Route::get('/patients/{patient}/passport', [PassportController::class, 'show'])->name('passport.show');
    Route::post('/symptom-reports', [SymptomReportController::class, 'store'])->name('symptom_reports.store');
});

// One-time Token-based Approvals (Doctors, Schools, Parents - No Login Required)
Route::get('/approve/{token}', [ApprovalController::class, 'show'])->name('approval.show');
Route::post('/approve/{token}', [ApprovalController::class, 'decide'])->name('approval.decide');
