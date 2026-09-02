<?php

namespace App\Http\Middleware;

use App\Models\Patient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePatientAuthenticated
{
    /**
     * Handle an incoming request for authenticated patient passport access.
     * Enforces strict cross-patient data isolation.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authenticatedPatientId = session('authenticated_patient_id');

        if (! $authenticatedPatientId) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect()->route('login')->with('warning', __('app.auth_required_message'));
        }

        // Verify patient isolation if route binds a patient parameter
        $routePatient = $request->route('patient');
        if ($routePatient) {
            $targetPatientId = $routePatient instanceof Patient ? $routePatient->id : (string) $routePatient;

            if ($targetPatientId !== $authenticatedPatientId) {
                // Deny access to other patients' sensitive clinical data
                abort(403, __('app.unauthorized_patient_access'));
            }
        }

        return $next($request);
    }
}
