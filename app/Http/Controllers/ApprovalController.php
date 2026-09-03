<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLink;
use App\Models\ApprovalRecord;
use App\Models\Patient;
use App\Traits\BuildsRecoveryContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    use BuildsRecoveryContext;

    /**
     * Display the one-time approval portal for doctors, school staff, or parents.
     */
    public function show(string $token): View
    {
        /** @var ApprovalLink|null $approvalLink */
        $approvalLink = ApprovalLink::where('token', $token)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        // 404 on used, expired, or non-existent token
        if (! $approvalLink) {
            abort(404, __('app.approval_token_invalid_or_used'));
        }

        $patient = $approvalLink->patient;

        // Stage 4 Gating Check: Final clearance cannot be submitted until Step 6 is reached
        if ($approvalLink->for_stage === 4 && ($patient->activity_step ?? 0) < 6) {
            abort(403, __('app.stage4_gate_step6_required'));
        }

        // Provide complete recovery context to the approver
        $context = $this->buildContext($patient);
        $context['approvalLink'] = $approvalLink;

        return view('approval.show', $context);
    }

    /**
     * Process an official decision (Approve or Reject) submitted via the one-time token.
     */
    public function decide(Request $request, string $token): View|RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'comments' => ['nullable', 'required_if:decision,rejected', 'string', 'max:1000'],
        ], [
            'comments.required_if' => __('app.comments_required_if_rejected'),
        ]);

        /** @var ApprovalLink|null $approvalLink */
        $approvalLink = ApprovalLink::where('token', $token)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (! $approvalLink) {
            abort(404, __('app.approval_token_invalid_or_used'));
        }

        $patient = $approvalLink->patient;

        // Enforce Stage 4 Step 6 prerequisite gate
        if ($approvalLink->for_stage === 4 && ($patient->activity_step ?? 0) < 6) {
            abort(403, __('app.stage4_gate_step6_required'));
        }

        // 1. Record decision in approval_records
        $context = $this->buildContext($patient);
        $aiRecommendation = $context['ai_insight'] ?? null;

        ApprovalRecord::create([
            'approval_link_id' => $approvalLink->id,
            'patient_id' => $patient->id,
            'stage' => $approvalLink->for_stage,
            'approver_role' => $approvalLink->approver_role,
            'decision' => $validated['decision'],
            'comments' => $validated['comments'] ?? null,
            'ai_recommendation' => $aiRecommendation,
            'decided_at' => now(),
        ]);

        // 2. Consume single-use token
        $approvalLink->update([
            'is_used' => true,
        ]);

        // 3. Multi-Party Consensus Check:
        // Check if ALL required approvers for this patient's current milestone cycle have approved.
        $targetStage = $approvalLink->for_stage;
        $currentLinks = ApprovalLink::where('patient_id', $patient->id)
            ->where('for_stage', $targetStage)
            ->orderBy('is_used', 'asc')
            ->orderByDesc('created_at')
            ->get()
            ->unique('approver_role');

        $currentLinkIds = $currentLinks->pluck('id');
        $totalRequiredLinks = $currentLinks->count();

        $approvedRecordsCount = ApprovalRecord::whereIn('approval_link_id', $currentLinkIds)
            ->where('decision', 'approved')
            ->count();

        $rejectedRecordsCount = ApprovalRecord::whereIn('approval_link_id', $currentLinkIds)
            ->where('decision', 'rejected')
            ->count();

        $milestoneAdvanced = false;

        // Advance milestone ONLY if EVERY required approver approved and NO rejection exists
        if ($totalRequiredLinks > 0 && $approvedRecordsCount === $totalRequiredLinks && $rejectedRecordsCount === 0) {
            $patient->current_stage = $targetStage;

            // Initialize activity step when advancing to Stage 3; clear when completing to Stage 4
            if ($patient->current_stage === 3) {
                $patient->activity_step = 1;
            } elseif ($patient->current_stage === 4) {
                $patient->activity_step = null;
            }

            $patient->save();
            $milestoneAdvanced = true;

            if ($patient->current_stage < 4) {
                $this->generateNextStageApprovalLinks($patient);
            }
        }

        return view('approval.confirmed', [
            'patient' => $patient,
            'approvalLink' => $approvalLink,
            'decision' => $validated['decision'],
            'comments' => $validated['comments'] ?? null,
            'milestoneAdvanced' => $milestoneAdvanced,
            'targetStage' => $targetStage,
        ]);
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
