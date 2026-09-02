<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Traits\BuildsRecoveryContext;
use Illuminate\View\View;

class PassportController extends Controller
{
    use BuildsRecoveryContext;

    /**
     * Display the Recovery Passport dashboard for the patient.
     */
    public function show(Patient $patient): View
    {
        $context = $this->buildContext($patient);

        return view('passport.show', $context);
    }
}
