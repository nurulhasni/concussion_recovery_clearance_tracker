@extends('layouts.app')

@section('title', 'Recovery Passport — ' . $patient->name)

@section('content')
<div class="space-y-6">

    <!-- 1. Patient Header Overview -->
    <div class="soft-card rounded-2xl p-5 sm:p-6 shadow-xl border border-slate-800 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-teal-500/20 to-teal-700/30 border border-teal-500/40 text-teal-300 font-extrabold text-xl flex items-center justify-center shadow-inner shrink-0">
                {{ strtoupper(substr($patient->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">{{ $patient->name }}</h1>
                    <span class="inline-flex items-center rounded-full bg-teal-950/80 px-3 py-0.5 text-xs font-semibold text-teal-300 border border-teal-500/30">
                        {{ __('app.milestone') }} {{ $patient->current_stage }} / 4
                    </span>
                </div>
                <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs sm:text-sm text-slate-400">
                    <span>{{ __('app.injury_type') }}: <strong class="text-slate-300">{{ $patient->injury_type }}</strong></span>
                    <span>&bull;</span>
                    <span>{{ __('app.injury_date') }}: <strong class="text-slate-300">{{ \Carbon\Carbon::parse($patient->injury_date)->format('M d, Y') }}</strong></span>
                    <span>&bull;</span>
                    <span class="text-teal-400 font-semibold">{{ $days_since_injury }} {{ __('app.days_in_recovery') }}</span>
                </div>
            </div>
        </div>

        <button onclick="document.getElementById('symptomModal').classList.remove('hidden')" 
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-500 hover:bg-teal-400 px-4 py-2.5 text-xs sm:text-sm font-bold text-slate-950 shadow-lg shadow-teal-950/40 transition shrink-0">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            {{ __('app.log_daily_symptoms') }}
        </button>
    </div>

    <!-- 2. Brief Dismissible Context Hint Banner -->
    <div id="hintBanner" class="rounded-xl border border-teal-500/30 bg-teal-950/40 p-4 text-xs sm:text-sm text-teal-200 flex items-center justify-between gap-3 shadow-md animate-in fade-in duration-200">
        <div class="flex items-center gap-3">
            <div class="p-1 rounded-lg bg-teal-500/20 text-teal-300 shrink-0">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="leading-relaxed">
                {{ __('app.passport_hint_banner') }}
            </p>
        </div>
        <button type="button" 
                onclick="document.getElementById('hintBanner').remove()" 
                class="text-teal-400 hover:text-teal-100 font-bold text-base px-2 py-0.5 rounded-lg hover:bg-teal-900/40 transition shrink-0"
                title="{{ __('app.dismiss') }}">
            &times;
        </button>
    </div>

    <!-- 3. Milestone & Physical Progression Tracker -->
    <div class="soft-card rounded-2xl p-5 sm:p-6 shadow-xl border border-slate-800">
        <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">{{ __('app.current_milestone') }}</h2>
        
        <!-- 4 Major Milestones Progress Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            @for($m = 1; $m <= 4; $m++)
                @php
                    $isCompleted = $patient->current_stage > $m;
                    $isCurrent = $patient->current_stage === $m;
                @endphp
                <div class="rounded-xl p-3.5 border transition {{ $isCurrent ? 'border-teal-500/60 bg-teal-950/30 shadow-md shadow-teal-950/20' : ($isCompleted ? 'border-emerald-500/30 bg-emerald-950/20' : 'border-slate-800 bg-slate-900/40 opacity-60') }}">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold uppercase tracking-wider {{ $isCurrent ? 'text-teal-400' : ($isCompleted ? 'text-emerald-400' : 'text-slate-500') }}">
                            Milestone {{ $m }}
                        </span>
                        @if($isCompleted)
                            <span class="text-emerald-400 text-xs font-semibold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Cleared
                            </span>
                        @elseif($isCurrent)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-teal-500/20 text-teal-300 border border-teal-500/40 animate-pulse">
                                Active
                            </span>
                        @endif
                    </div>
                    <div class="text-sm font-bold text-slate-100 mb-1">
                        {{ __('app.milestone_' . $m) }}
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ __('app.milestone_desc_' . $m) }}
                    </p>
                </div>
            @endfor
        </div>

        <!-- Stage 3 Sub-Progression: CDC 6-Step Return to Play Progression -->
        @if($patient->current_stage === 3)
            <div class="pt-5 border-t border-slate-800/80">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        {{ __('app.physical_progression') }}
                    </h3>
                    <span class="text-xs text-amber-300/80 font-medium">
                        Current: <strong>{{ __('app.step_' . ($patient->activity_step ?? 1)) }}</strong>
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
                    @for($s = 1; $s <= 6; $s++)
                        @php
                            $stepDone = ($patient->activity_step ?? 1) >= $s;
                            $isCurrentStep = ($patient->activity_step ?? 1) === $s;
                        @endphp
                        <div class="rounded-lg p-2.5 border text-center transition {{ $isCurrentStep ? 'border-amber-500/70 bg-amber-950/40 shadow-sm' : ($stepDone ? 'border-emerald-500/40 bg-emerald-950/20' : 'border-slate-800 bg-slate-900/30 opacity-50') }}">
                            <div class="text-xs font-bold {{ $isCurrentStep ? 'text-amber-300' : ($stepDone ? 'text-emerald-400' : 'text-slate-500') }}">
                                {{ __('app.step') }} {{ $s }}
                            </div>
                            <div class="text-xs font-semibold text-slate-200 mt-0.5 truncate" title="{{ __('app.step_' . $s) }}">
                                {{ __('app.step_' . $s) }}
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        @endif
    </div>

    <!-- 4. Grid: Symptom Trend + Restrictions + AI Layperson Insight -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- 4A. Symptom Trend (7 Days) with Inline Legend -->
        <div class="soft-card rounded-2xl p-5 shadow-xl border border-slate-800 flex flex-col justify-between">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5 mb-3">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-300">{{ __('app.symptom_tracking') }}</h2>
                        <!-- Clear Inline Legend for Severity Abbreviations -->
                        <div class="flex items-center gap-2 text-xs text-slate-400 mt-0.5">
                            <span class="inline-flex items-center gap-1 font-medium"><span class="h-2 w-2 rounded-full bg-emerald-400 inline-block"></span>MIL (Mild)</span>
                            <span class="inline-flex items-center gap-1 font-medium"><span class="h-2 w-2 rounded-full bg-amber-400 inline-block"></span>MOD (Moderate)</span>
                            <span class="inline-flex items-center gap-1 font-medium"><span class="h-2 w-2 rounded-full bg-rose-500 inline-block"></span>SEV (Severe)</span>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full self-start sm:self-auto {{ $trend_direction === 'improving' ? 'bg-emerald-950 text-emerald-400 border border-emerald-500/30' : ($trend_direction === 'worsening' ? 'bg-rose-950 text-rose-400 border border-rose-500/30' : 'bg-slate-800 text-slate-300 border border-slate-700') }}">
                        {{ __('app.trend_' . $trend_direction) }}
                    </span>
                </div>

                @if($recent_reports->count() > 0)
                    <!-- Severity Bars Grid -->
                    <div class="grid grid-cols-7 gap-1.5 my-4">
                        @foreach($recent_reports->take(7)->reverse() as $report)
                            @php
                                $colorClass = match($report->ai_severity) {
                                    'mild' => 'bg-emerald-500 border-emerald-400 text-slate-950',
                                    'moderate' => 'bg-amber-400 border-amber-300 text-slate-950',
                                    'severe' => 'bg-rose-500 border-rose-400 text-white',
                                    default => 'bg-slate-600 border-slate-500 text-white',
                                };
                            @endphp
                            <div class="flex flex-col items-center">
                                <div class="w-full h-14 rounded-lg {{ $colorClass }} flex flex-col items-center justify-center p-1 text-xs font-bold shadow transition hover:scale-105"
                                     title="{{ $report->report_text }}">
                                    <span>{{ substr(strtoupper($report->ai_severity ?? 'M'), 0, 3) }}</span>
                                    @if($report->ai_red_flag)
                                        <span class="text-xs text-rose-900 bg-rose-200 rounded-full px-1.5 mt-0.5 font-extrabold">!</span>
                                    @endif
                                </div>
                                <span class="text-xs text-slate-400 mt-1">
                                    {{ \Carbon\Carbon::parse($report->reported_at)->format('d/m') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Latest Report Snippet -->
                    <div class="rounded-xl bg-slate-900/70 p-3 border border-slate-800/80 text-xs text-slate-300">
                        <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                            <span>{{ __('app.reported_on') }}: {{ \Carbon\Carbon::parse($recent_reports->first()->reported_at)->diffForHumans() }}</span>
                            @if($recent_reports->first()->ai_safety_override)
                                <span class="text-amber-400 font-semibold">{{ __('app.safety_override_active') }}</span>
                            @endif
                        </div>
                        <p class="italic text-slate-300 line-clamp-2">"{{ $recent_reports->first()->report_text }}"</p>
                    </div>
                @else
                    <p class="text-xs text-slate-500 py-6 text-center">{{ __('app.no_reports_yet') }}</p>
                @endif
            </div>
        </div>

        <!-- 4B. Active Restrictions -->
        <div class="soft-card rounded-2xl p-5 shadow-xl border border-slate-800">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">{{ __('app.active_restrictions') }}</h2>
            @if(count($active_restrictions) > 0)
                <div class="space-y-2">
                    @foreach($active_restrictions as $restrictionCode)
                        <div class="flex items-center gap-2.5 rounded-xl border border-amber-500/20 bg-amber-950/20 px-3.5 py-2.5 text-xs sm:text-sm text-amber-200">
                            <svg class="h-4 w-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="font-medium">{{ __('app.restriction_' . $restrictionCode) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex items-center gap-2.5 rounded-xl border border-emerald-500/30 bg-emerald-950/20 px-3.5 py-4 text-xs sm:text-sm text-emerald-300">
                    <svg class="h-5 w-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ __('app.no_restrictions') }}</span>
                </div>
            @endif
        </div>

        <!-- 4C. AI Layperson Insight & Safe Summary -->
        <div class="soft-card rounded-2xl p-5 shadow-xl border border-slate-800 flex flex-col justify-between">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-teal-400 flex items-center gap-1.5 mb-3">
                    <svg class="h-4 w-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    {{ __('app.ai_insight') }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-200 leading-relaxed font-normal">
                    {{ $ai_insight }}
                </p>
            </div>

            <!-- Mandatory Assistive Disclaimer -->
            @include('partials.ai-disclaimer')
        </div>

    </div>

    <!-- 5. Real Patient-Facing Approvals Status Card (Status Only, No Raw Links) -->
    <div class="soft-card rounded-2xl p-5 sm:p-6 shadow-xl border border-slate-800">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <div>
                <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-300">
                    {{ __('app.approval_for_stage', ['stage' => min(4, $current_stage + 1)]) }}
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ __('app.patient_status_only_notice') }}
                </p>
            </div>
            @if($all_approvals_ready)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-950 px-3 py-1 text-xs font-bold text-emerald-300 border border-emerald-500/40">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Milestone Advancement Ready
                </span>
            @endif
        </div>

        @if(count($approval_statuses) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($approval_statuses as $approval)
                    @php
                        $statusBadge = match($approval['status']) {
                            'approved' => 'bg-emerald-950 text-emerald-300 border-emerald-500/40',
                            'rejected' => 'bg-rose-950 text-rose-300 border-rose-500/40',
                            default => 'bg-amber-950 text-amber-300 border-amber-500/40',
                        };
                    @endphp
                    <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs sm:text-sm font-bold text-slate-200">{{ __('app.role_' . $approval['role']) }}</span>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold border {{ $statusBadge }}">
                                {{ __('app.status_' . $approval['status']) }}
                            </span>
                        </div>
                        <div class="text-xs sm:text-sm text-slate-400">
                            Approver: <strong class="text-slate-300">{{ $approval['name'] }}</strong>
                        </div>
                        @if($approval['decided_at'])
                            <div class="text-xs text-slate-400 mt-1">
                                {{ __('app.timestamp') }}: {{ \Carbon\Carbon::parse($approval['decided_at'])->format('M d, H:i') }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-xs text-slate-500 py-3">{{ __('app.no_restrictions') }}</p>
        @endif
    </div>

    <!-- 6. Demo & Judging Tools Panel (Clearly Separated Scaffolding at Bottom) -->
    @php
        $pendingApprovals = array_filter($approval_statuses, fn($a) => $a['status'] === 'pending' && empty($a['is_used']));
    @endphp
    @if(count($pendingApprovals) > 0)
        <div class="rounded-2xl border border-indigo-500/30 bg-gradient-to-b from-indigo-950/30 to-slate-950/60 p-5 sm:p-6 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-3 border-b border-indigo-500/20">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="rounded-md bg-indigo-500/20 px-2 py-0.5 text-xs font-bold text-indigo-300 border border-indigo-500/40 uppercase tracking-wider">
                            {{ __('app.demo_judging_panel_badge') }}
                        </span>
                        <h3 class="text-xs sm:text-sm font-bold text-indigo-200">
                            {{ __('app.demo_judging_panel_title') }}
                        </h3>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-3xl">
                        {{ __('app.demo_judging_panel_desc') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                @foreach($pendingApprovals as $approval)
                    <div class="rounded-xl border border-indigo-500/20 bg-slate-900/80 p-3.5 flex flex-col justify-between">
                        <div class="mb-3">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-indigo-300">{{ __('app.role_' . $approval['role']) }}</span>
                                <span class="text-xs text-amber-400 font-semibold">{{ __('app.status_pending') }}</span>
                            </div>
                            <div class="text-xs text-slate-300 truncate" title="{{ $approval['name'] }}">
                                {{ $approval['name'] }}
                            </div>
                        </div>

                        <a href="{{ $approval['link_url'] }}" target="_blank"
                           class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 px-3 py-2 text-xs font-bold text-white shadow-md transition">
                            <span>{{ __('app.open_link') }}</span>
                            <svg class="h-3.5 w-3.5 text-indigo-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

<!-- Symptom Logging Modal -->
<div id="symptomModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
    <div class="soft-card rounded-2xl p-6 max-w-lg w-full shadow-2xl border border-slate-700 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <svg class="h-5 w-5 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                {{ __('app.log_daily_symptoms') }}
            </h3>
            <button onclick="document.getElementById('symptomModal').classList.add('hidden')" class="text-slate-400 hover:text-white text-lg font-bold">
                &times;
            </button>
        </div>

        <form method="POST" action="{{ route('symptom_reports.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="patient_id" value="{{ $patient->id }}">

            <div>
                <label for="report_text" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Daily Symptom Observations (Free-Text)
                </label>
                <textarea id="report_text" name="report_text" rows="4" required
                          placeholder="{{ __('app.enter_symptoms_placeholder') }}"
                          class="w-full rounded-xl border border-slate-700 bg-slate-900 px-3.5 py-2.5 text-xs sm:text-sm text-slate-100 placeholder-slate-500 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition"></textarea>
            </div>

            <!-- Quick Template Prompt Helper -->
            <div class="flex flex-wrap gap-1.5 text-xs text-slate-400">
                <span class="font-semibold text-slate-400">Quick suggestions:</span>
                <button type="button" onclick="document.getElementById('report_text').value = 'Feeling fine today. Walked for 20 minutes with zero headache, dizziness, or visual fatigue.'" 
                        class="text-teal-400 hover:underline bg-slate-900 px-2 py-0.5 rounded border border-slate-800">
                    Mild / Improving
                </button>
                <button type="button" onclick="document.getElementById('report_text').value = 'Had mild headache after 30 minutes of homework, subsided after 10 minutes of rest. No nausea.'" 
                        class="text-amber-400 hover:underline bg-slate-900 px-2 py-0.5 rounded border border-slate-800">
                    Moderate
                </button>
                <button type="button" onclick="document.getElementById('report_text').value = 'Severe headache suddenly worsened, repeated vomiting twice this morning, feeling very confused.'" 
                        class="text-rose-400 hover:underline bg-slate-900 px-2 py-0.5 rounded border border-slate-800">
                    Red Flag / Danger
                </button>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('symptomModal').classList.add('hidden')" 
                        class="rounded-xl border border-slate-700 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="rounded-xl bg-teal-500 hover:bg-teal-400 px-4 py-2 text-xs font-bold text-slate-950 shadow transition">
                    Analyze & Submit Report
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
