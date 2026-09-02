<?php

namespace App\Traits;

use App\Models\Patient;
use App\Services\SymptomAnalyzer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

trait BuildsRecoveryContext
{
    /**
     * Build comprehensive recovery context for a patient.
     * Shared across PassportController and ApprovalController.
     *
     * @return array{
     *     patient: Patient,
     *     days_since_injury: int,
     *     current_stage: int,
     *     activity_step: int|null,
     *     milestone_name: string,
     *     recent_reports: Collection,
     *     trend_summary: string,
     *     trend_direction: string,
     *     active_restrictions: array<string>,
     *     approval_statuses: array<array{
     *         role: string,
     *         name: string,
     *         status: string,
     *         token?: string,
     *         link_url?: string,
     *         decided_at?: \Carbon\Carbon|null
     *     }>,
     *     ai_insight: string,
     *     all_approvals_ready: bool
     * }
     */
    public function buildContext(Patient $patient): array
    {
        $locale = App::getLocale();
        $daysSinceInjury = max(1, (int) Carbon::parse($patient->injury_date)->diffInDays(now()) + 1);

        // 1. Resolve 7-day symptom reports & trend
        $recentReports = $patient->symptomReports()
            ->take(7)
            ->get();

        $trendDirection = $this->resolveTrendDirection($recentReports);

        // 2. Resolve Active Restrictions from config/recovery_restrictions.php
        $restrictionsConfig = config('recovery_restrictions', []);
        $activeRestrictions = [];

        if ($patient->current_stage === 3 && $patient->activity_step !== null) {
            $activeRestrictions = $restrictionsConfig[3][$patient->activity_step] ?? [];
        } else {
            $activeRestrictions = $restrictionsConfig[$patient->current_stage] ?? [];
        }

        // 3. Resolve Approval Status for the upcoming milestone (current_stage + 1)
        $targetStage = $patient->current_stage + 1;
        $approvalLinks = $patient->approvalLinks()
            ->where('for_stage', $targetStage)
            ->orderBy('is_used', 'asc')
            ->orderByDesc('created_at')
            ->get()
            ->unique('approver_role')
            ->values();

        $linkIds = $approvalLinks->pluck('id');
        $approvalRecords = $patient->approvalRecords()
            ->whereIn('approval_link_id', $linkIds)
            ->get()
            ->keyBy('approval_link_id');

        $approvalStatuses = [];
        $approvedCount = 0;
        $totalRequired = $approvalLinks->count();

        foreach ($approvalLinks as $link) {
            $record = $approvalRecords->get($link->id);
            $status = 'pending';
            $decidedAt = null;

            if ($record) {
                $status = $record->decision; // 'approved' or 'rejected'
                $decidedAt = $record->decided_at;
            } elseif ($link->is_used) {
                $status = 'processed';
            } elseif (Carbon::parse($link->expires_at)->isPast()) {
                $status = 'expired';
            }

            if ($status === 'approved') {
                $approvedCount++;
            }

            $approvalStatuses[] = [
                'role' => $link->approver_role,
                'name' => $link->approver_name,
                'status' => $status,
                'token' => $link->token,
                'link_url' => url('/approve/'.$link->token),
                'is_used' => $link->is_used,
                'expires_at' => $link->expires_at,
                'decided_at' => $decidedAt,
            ];
        }

        $allApprovalsReady = ($totalRequired > 0 && $approvedCount === $totalRequired);

        // 4. Generate AI layperson summary
        /** @var SymptomAnalyzer $analyzer */
        $analyzer = app(SymptomAnalyzer::class);
        $latestReport = $recentReports->first();
        $latestExtraction = [
            'extracted_symptoms' => $latestReport?->extracted_symptoms ?? [],
            'severity' => $latestReport?->ai_severity ?? 'mild',
        ];

        $aiInsight = $analyzer->generateSummary($patient, $latestExtraction, $locale);

        return [
            'patient' => $patient,
            'days_since_injury' => $daysSinceInjury,
            'current_stage' => $patient->current_stage,
            'activity_step' => $patient->activity_step,
            'milestone_name' => "milestone_{$patient->current_stage}",
            'recent_reports' => $recentReports,
            'trend_direction' => $trendDirection,
            'active_restrictions' => $activeRestrictions,
            'approval_statuses' => $approvalStatuses,
            'ai_insight' => $aiInsight,
            'all_approvals_ready' => $allApprovalsReady,
            'target_stage' => $targetStage,
        ];
    }

    /**
     * Determine trend direction label (improving/stable/worsening).
     *
     * @param  Collection  $reports
     */
    protected function resolveTrendDirection($reports): string
    {
        if ($reports->count() < 2) {
            return 'baseline';
        }

        $scores = ['mild' => 1, 'moderate' => 2, 'severe' => 3];
        $reportsList = $reports->values();
        $newest = $scores[$reportsList->first()->ai_severity ?? 'mild'] ?? 1;
        $oldest = $scores[$reportsList->last()->ai_severity ?? 'mild'] ?? 1;

        if ($newest < $oldest) {
            return 'improving';
        }

        if ($newest > $oldest) {
            return 'worsening';
        }

        return 'stable';
    }
}
