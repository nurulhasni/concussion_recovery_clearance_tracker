@extends('layouts.app')

@section('title', 'Clearance Review — ' . $patient->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- 1. Review Portal Header -->
    <div class="soft-card rounded-2xl p-6 shadow-xl border border-slate-800">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-950 px-3 py-1 text-xs font-bold text-teal-300 border border-teal-500/30 mb-2">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    {{ __('app.role_' . $approvalLink->approver_role) }} Review Portal
                </span>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">
                    {{ __('app.approval_for_stage', ['stage' => $approvalLink->for_stage]) }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Reviewing patient: <strong class="text-slate-200">{{ $patient->name }}</strong> &middot; 
                    Approver: <strong class="text-teal-300">{{ $approvalLink->approver_name }}</strong>
                </p>
            </div>
            <div class="text-right text-xs sm:text-sm text-slate-400 bg-slate-900/80 p-3 rounded-xl border border-slate-800 shrink-0">
                <div>{{ __('app.injury_type') }}: <strong class="text-slate-200">{{ $patient->injury_type }}</strong></div>
                <div>{{ __('app.days_in_recovery') }}: <strong class="text-teal-400">{{ $days_since_injury }} Days</strong></div>
                <div>Current Stage: <strong class="text-amber-400">Milestone {{ $patient->current_stage }}</strong></div>
            </div>
        </div>
    </div>

    <!-- 2. Complete Recovery Context for Informed Clinical Decision -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- 2A. 7-Day Symptom History & Trajectory -->
        <div class="soft-card rounded-2xl p-5 shadow-xl border border-slate-800">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('app.symptom_tracking') }}</h2>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $trend_direction === 'improving' ? 'bg-emerald-950 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-300' }}">
                    {{ __('app.trend_' . $trend_direction) }}
                </span>
            </div>

            @if($recent_reports->count() > 0)
                <div class="grid grid-cols-7 gap-1.5 my-3">
                    @foreach($recent_reports->take(7)->reverse() as $report)
                        @php
                            $colorClass = match($report->ai_severity) {
                                'mild' => 'bg-emerald-500 text-slate-950',
                                'moderate' => 'bg-amber-400 text-slate-950',
                                'severe' => 'bg-rose-500 text-white',
                                default => 'bg-slate-600 text-white',
                            };
                        @endphp
                        <div class="flex flex-col items-center">
                            <div class="w-full h-12 rounded-lg {{ $colorClass }} flex items-center justify-center text-xs font-bold shadow"
                                 title="{{ $report->report_text }}">
                                {{ substr(strtoupper($report->ai_severity ?? 'M'), 0, 3) }}
                            </div>
                            <span class="text-xs text-slate-400 mt-1">
                                {{ \Carbon\Carbon::parse($report->reported_at)->format('d/m') }}
                            </span>
                        </div>
                    @endforeach
                </div>
                <div class="space-y-2 mt-3 max-h-44 overflow-y-auto pr-1">
                    @foreach($recent_reports->take(3) as $report)
                        <div class="p-2.5 rounded-lg bg-slate-900/80 border border-slate-800 text-xs">
                            <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                                <span>{{ \Carbon\Carbon::parse($report->reported_at)->format('M d, H:i') }}</span>
                                <span class="font-semibold text-slate-300 uppercase">{{ $report->ai_severity }}</span>
                            </div>
                            <p class="text-slate-300 italic">"{{ $report->report_text }}"</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-500 py-6 text-center">{{ __('app.no_reports_yet') }}</p>
            @endif
        </div>

        <!-- 2B. AI Layperson Summary & Restrictions -->
        <div class="soft-card rounded-2xl p-5 shadow-xl border border-slate-800 flex flex-col justify-between">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-teal-400 mb-2 flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('app.ai_insight') }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-200 leading-relaxed bg-slate-900/60 p-3 rounded-xl border border-slate-800/80">
                    {{ $ai_insight }}
                </p>

                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mt-4 mb-2">
                    Active Restrictions at Current Stage
                </h2>
                <div class="flex flex-wrap gap-1.5">
                    @forelse($active_restrictions as $res)
                        <span class="inline-flex items-center rounded-lg bg-amber-950/30 px-2.5 py-1 text-xs font-medium text-amber-200 border border-amber-500/20">
                            {{ __('app.restriction_' . $res) }}
                        </span>
                    @empty
                        <span class="text-xs text-emerald-400 font-medium">No active restrictions</span>
                    @endforelse
                </div>
            </div>

            @include('partials.ai-disclaimer')
        </div>

    </div>

    <!-- 3. Official Clearance Decision Form -->
    <div class="soft-card rounded-2xl p-6 shadow-2xl border border-teal-500/30 bg-slate-900/90">
        <h2 class="text-base font-bold text-white mb-1">
            Submit Clearance Decision for Milestone {{ $approvalLink->for_stage }}
        </h2>
        <p class="text-xs text-slate-400 mb-5">
            Your decision will be timestamped and recorded in the permanent clinical audit log.
        </p>

        <form id="decisionForm" method="POST" action="{{ route('approval.decide', $approvalLink->token) }}" class="space-y-5">
            @csrf

            <!-- Decision Options -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="relative flex cursor-pointer rounded-xl border border-emerald-500/40 bg-emerald-950/20 p-4 hover:bg-emerald-950/40 transition focus:outline-none">
                    <input type="radio" name="decision" value="approved" required class="mt-0.5 text-emerald-500 focus:ring-emerald-400">
                    <div class="ml-3">
                        <span class="block text-sm font-bold text-emerald-300">{{ __('app.approve_action') }}</span>
                        <span class="block text-xs text-slate-400 mt-0.5">Patient meets protocol prerequisites to advance to Milestone {{ $approvalLink->for_stage }}.</span>
                    </div>
                </label>

                <label class="relative flex cursor-pointer rounded-xl border border-rose-500/40 bg-rose-950/20 p-4 hover:bg-rose-950/40 transition focus:outline-none">
                    <input type="radio" name="decision" value="rejected" required class="mt-0.5 text-rose-500 focus:ring-rose-400">
                    <div class="ml-3">
                        <span class="block text-sm font-bold text-rose-300">{{ __('app.reject_action') }}</span>
                        <span class="block text-xs text-slate-400 mt-0.5">Symptoms or clinical concerns require holding at current stage.</span>
                    </div>
                </label>
            </div>

            <!-- Notes / Comments -->
            <div>
                <label for="comments" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    {{ __('app.decision_notes') }} (Optional)
                </label>
                <textarea id="comments" name="comments" rows="3"
                          placeholder="Provide clinical observations, specific accommodation adjustments, or instructions for parents/school..."
                          class="w-full rounded-xl border border-slate-700 bg-slate-900 px-3.5 py-2 text-xs sm:text-sm text-slate-100 placeholder-slate-500 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition"></textarea>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-800">
                <span class="text-xs text-slate-400">
                    🔒 Single-use token will be consumed upon submission.
                </span>
                <button type="submit" id="submitDecisionBtn" class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-500 hover:bg-teal-400 px-6 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-teal-950/50 transition disabled:opacity-75 disabled:cursor-not-allowed">
                    <svg id="submitDecisionSpinner" class="hidden animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="submitDecisionText">{{ __('app.submit_decision') }}</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    document.getElementById('decisionForm')?.addEventListener('submit', function() {
        const btn = document.getElementById('submitDecisionBtn');
        const spinner = document.getElementById('submitDecisionSpinner');
        const text = document.getElementById('submitDecisionText');
        if (btn) {
            btn.disabled = true;
            spinner?.classList.remove('hidden');
            if (text) text.textContent = '{{ __('app.submitting_decision') }}';
        }
    });
</script>
@endsection
