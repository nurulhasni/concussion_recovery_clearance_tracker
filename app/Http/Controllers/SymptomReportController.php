<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLink;
use App\Models\Patient;
use App\Models\SymptomReport;
use App\Services\SymptomAnalyzer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class SymptomReportController extends Controller
{
    public function __construct(
        protected SymptomAnalyzer $analyzer
    ) {}

    /**
     * Store a new symptom report, run AI analysis + keyword guardrails,
     * and apply clinical step progression or safety downgrade rules.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'uuid', 'exists:patients,id'],
            'report_text' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        /** @var Patient $patient */
        $patient = Patient::findOrFail($validated['patient_id']);
        $locale = App::getLocale();

        // 1. Run 2-stage AI analysis pipeline with keyword safety check
        $analysis = $this->analyzer->extractSymptoms($validated['report_text'], $locale);

        // 2. Persist the symptom report record
        $report = SymptomReport::create([
            'patient_id' => $patient->id,
            'report_text' => $validated['report_text'],
            'ai_severity' => $analysis['severity'] ?? 'mild',
            'ai_red_flag' => (bool) ($analysis['red_flag'] ?? false),
            'ai_safety_override' => (bool) ($analysis['safety_override'] ?? false),
            'extracted_symptoms' => $analysis['extracted_symptoms'] ?? [],
            'reported_at' => now(),
        ]);

        // 3. Clinical step progression and safety auto-downgrade logic
        $isRedFlag = (bool) ($analysis['red_flag'] ?? false);
        $stageChanged = false;
        $stepChanged = false;
        $previousStage = $patient->current_stage;
        $previousStep = $patient->activity_step;

        if ($patient->current_stage === 3) {
            // Milestone 3: Graduated Physical Activity (CDC 6-Step Return to Play)
            $currentStep = $patient->activity_step ?? 1;

            if ($isRedFlag) {
                // Symptoms recurred / red flag detected: drop activity step by 1
                if ($currentStep > 1) {
                    $patient->activity_step = $currentStep - 1;
                    $stepChanged = true;
                } else {
                    // Step is already at minimum (1) and red flag persists -> auto-downgrade to Milestone 2 (School Recovery)
                    $patient->current_stage = 2;
                    $patient->activity_step = null;
                    $stageChanged = true;
                }
            } else {
                // Symptom-free / mild tolerated progression: advance 1 step (max 6)
                if ($currentStep < 6) {
                    $patient->activity_step = $currentStep + 1;
                    $stepChanged = true;
                }
            }
        } else {
            // Milestones 2 or 4: Auto-downgrade stage if severe symptoms / red flags emerge
            if ($isRedFlag && $patient->current_stage > 1) {
                $patient->current_stage = $patient->current_stage - 1;
                if ($patient->current_stage === 3) {
                    $patient->activity_step = 6; // Set to step 6 when falling back from stage 4
                }
                $stageChanged = true;
            }
        }

        $patient->save();

        // Ensure approval links for the upcoming milestone exist without duplicate accumulation
        if ($isRedFlag && $stageChanged && $patient->current_stage < $previousStage) {
            $this->generateNextStageApprovalLinks($patient);
        }

        // 4. Build user feedback message
        $feedbackKey = 'success';
        $message = __('app.report_logged_successfully');

        if ($isRedFlag) {
            $feedbackKey = 'warning';
            $message = __('app.report_red_flag_alert');
            if ($stageChanged) {
                $message .= ' '.__('app.stage_downgraded_alert', ['stage' => $patient->current_stage]);
            } elseif ($stepChanged) {
                $message .= ' '.__('app.step_downgraded_alert', ['step' => $patient->activity_step]);
            }
        } elseif ($stepChanged && $patient->current_stage === 3) {
            $message .= ' '.__('app.step_advanced_alert', ['step' => $patient->activity_step]);
        }

        return redirect()->route('passport.show', $patient)->with($feedbackKey, $message);
    }

    /**
     * Generate new approval links for the patient's next milestone stage if none exist and are active.
     */
    private function generateNextStageApprovalLinks(Patient $patient): void
    {
        if ($patient->current_stage >= 4) {
            return;
        }

        $nextStage = $patient->current_stage + 1;
        $roles = config('milestone_approvers.'.$nextStage, []);

        foreach ($roles as $role) {
            // Check if an active, unused link already exists for this stage & role
            $existingLink = ApprovalLink::where('patient_id', $patient->id)
                ->where('for_stage', $nextStage)
                ->where('approver_role', $role)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->first();

            if ($existingLink) {
                continue;
            }

            // Reuse patient's most recent approver_name for this role to maintain continuity
            $latestLink = ApprovalLink::where('patient_id', $patient->id)
                ->where('approver_role', $role)
                ->orderByDesc('created_at')
                ->first();

            $approverName = $latestLink?->approver_name;

            if (! $approverName) {
                $approverName = match ($role) {
                    'doctor' => 'Assigned Doctor',
                    'school' => 'Assigned School Staff',
                    'parent' => 'Assigned Parent/Guardian',
                    default => 'Assigned '.ucfirst($role),
                };
            }

            ApprovalLink::create([
                'patient_id' => $patient->id,
                'for_stage' => $nextStage,
                'approver_role' => $role,
                'approver_name' => $approverName,
                'token' => Str::random(64),
                'expires_at' => now()->addDays(7),
                'is_used' => false,
            ]);
        }
    }
}
