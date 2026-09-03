@extends('layouts.app')

@section('title', 'Recovery Passport — ' . $patient->name)

@section('content')
<div class="space-y-6">

    <!-- 1. Patient Header Overview -->
    <div class="soft-card rounded-2xl p-5 sm:p-6 shadow-xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-2xl bg-gradient-to-tr from-teal-500 to-cyan-400 text-slate-950 font-black text-xl flex items-center justify-center shadow-lg shadow-teal-500/20 ring-2 ring-white/10 shrink-0">
                {{ strtoupper(substr($patient->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">{{ $patient->name }}</h1>
                    <span class="inline-flex items-center gap-1 rounded-full bg-teal-950/80 px-3 py-0.5 text-xs font-semibold text-teal-300 border border-teal-500/40 shadow-sm">
                        <span class="h-1.5 w-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                        {{ __('app.milestone') }} {{ $patient->current_stage }} / 4
                    </span>
                </div>
                <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs sm:text-sm text-slate-400">
                    <span>{{ __('app.injury_type') }}: <strong class="text-slate-200">{{ $patient->injury_type }}</strong></span>
                    <span class="text-slate-600">&bull;</span>
                    <span>{{ __('app.injury_date') }}: <strong class="text-slate-200">{{ \Carbon\Carbon::parse($patient->injury_date)->format('M d, Y') }}</strong></span>
                    <span class="text-slate-600">&bull;</span>
                    <span class="text-teal-400 font-semibold flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ $days_since_injury }} {{ __('app.days_in_recovery') }}
                    </span>
                </div>
            </div>
        </div>

        <button onclick="document.getElementById('symptomModal').classList.remove('hidden')" 
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-teal-500 to-teal-400 hover:from-teal-400 hover:to-teal-300 px-5 py-2.5 text-xs sm:text-sm font-bold text-slate-950 shadow-lg shadow-teal-950/50 hover:shadow-teal-500/20 hover:scale-[1.02] active:scale-[0.98] transition transform shrink-0">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            {{ __('app.log_daily_symptoms') }}
        </button>
    </div>

    <!-- 2. Brief Dismissible Context Hint Banner -->
    <div id="hintBanner" class="rounded-xl border border-teal-500/30 bg-gradient-to-r from-teal-950/50 via-teal-900/20 to-slate-950/60 p-4 text-xs sm:text-sm text-teal-200 flex items-center justify-between gap-3 shadow-md">
        <div class="flex items-center gap-3">
            <div class="p-1.5 rounded-lg bg-teal-500/20 text-teal-300 shrink-0">
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
    <div class="soft-card rounded-2xl p-5 sm:p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                <svg class="h-4 w-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                {{ __('app.current_milestone') }}
            </h2>
            <span class="text-xs text-slate-400">PedsConcussion & CDC Standard</span>
        </div>
        
        <!-- 4 Major Milestones Stepper -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            @for($m = 1; $m <= 4; $m++)
                @php
                    $isCompleted = $patient->current_stage > $m;
                    $isCurrent = $patient->current_stage === $m;
                @endphp
                <div class="rounded-xl p-4 border transition {{ $isCurrent ? 'border-teal-500 bg-gradient-to-b from-teal-950/40 to-slate-900/70 shadow-lg shadow-teal-950/30 ring-1 ring-teal-500/40' : ($isCompleted ? 'border-emerald-500/40 bg-gradient-to-b from-emerald-950/25 to-slate-900/50' : 'border-white/[0.06] bg-slate-900/30 opacity-70') }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider {{ $isCurrent ? 'text-teal-300' : ($isCompleted ? 'text-emerald-300' : 'text-slate-500') }}">
                            Milestone {{ $m }}
                        </span>
                        @if($isCompleted)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-950/80 px-2 py-0.5 text-[11px] font-bold text-emerald-300 border border-emerald-500/40">
                                <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Cleared
                            </span>
                        @elseif($isCurrent)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/40">
                                <span class="h-1.5 w-1.5 rounded-full bg-teal-400 animate-ping"></span>
                                Active
                            </span>
                        @else
                            <span class="text-[11px] font-semibold text-slate-500">Upcoming</span>
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
                        <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        {{ __('app.physical_progression') }}
                    </h3>
                    <span class="text-xs text-amber-300 font-medium">
                        Current: <strong class="text-amber-200">{{ __('app.step_' . ($patient->activity_step ?? 1)) }}</strong>
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
                    @for($s = 1; $s <= 6; $s++)
                        @php
                            $stepDone = ($patient->activity_step ?? 1) >= $s;
                            $isCurrentStep = ($patient->activity_step ?? 1) === $s;
                        @endphp
                        <div class="rounded-xl p-2.5 border text-center transition {{ $isCurrentStep ? 'border-amber-500/80 bg-amber-950/40 shadow-sm ring-1 ring-amber-500/30' : ($stepDone ? 'border-emerald-500/40 bg-emerald-950/20' : 'border-white/[0.05] bg-slate-900/30 opacity-60') }}">
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
        <div class="soft-card rounded-2xl p-5 shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5 mb-3">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-200">{{ __('app.symptom_tracking') }}</h2>
                        <!-- Clear Inline Legend for Severity Abbreviations -->
                        <div class="flex items-center gap-2 text-xs text-slate-400 mt-0.5">
                            <span class="inline-flex items-center gap-1 font-medium"><span class="h-2 w-2 rounded-full bg-emerald-400 inline-block shadow-sm shadow-emerald-400/50"></span>MIL (Mild)</span>
                            <span class="inline-flex items-center gap-1 font-medium"><span class="h-2 w-2 rounded-full bg-amber-400 inline-block shadow-sm shadow-amber-400/50"></span>MOD (Moderate)</span>
                            <span class="inline-flex items-center gap-1 font-medium"><span class="h-2 w-2 rounded-full bg-rose-500 inline-block shadow-sm shadow-rose-500/50"></span>SEV (Severe)</span>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full self-start sm:self-auto {{ $trend_direction === 'improving' ? 'bg-emerald-950 text-emerald-300 border border-emerald-500/40' : ($trend_direction === 'worsening' ? 'bg-rose-950 text-rose-300 border border-rose-500/40' : 'bg-slate-800 text-slate-300 border border-slate-700') }}">
                        {{ __('app.trend_' . $trend_direction) }}
                    </span>
                </div>

                @if($recent_reports->count() > 0)
                    <!-- Severity Bars Grid -->
                    <div class="grid grid-cols-7 gap-1.5 my-4">
                        @foreach($recent_reports->take(7)->reverse() as $report)
                            @php
                                $colorClass = match($report->ai_severity) {
                                    'mild' => 'bg-gradient-to-b from-emerald-400 to-emerald-600 border-emerald-400/80 text-slate-950',
                                    'moderate' => 'bg-gradient-to-b from-amber-300 to-amber-500 border-amber-300/80 text-slate-950',
                                    'severe' => 'bg-gradient-to-b from-rose-400 to-rose-600 border-rose-400/80 text-white',
                                    default => 'bg-slate-600 border-slate-500 text-white',
                                };
                            @endphp
                            <div class="flex flex-col items-center">
                                <div class="w-full h-14 rounded-xl {{ $colorClass }} flex flex-col items-center justify-center p-1 text-xs font-black shadow-md transition hover:scale-105"
                                     title="{{ $report->report_text }}">
                                    <span>{{ substr(strtoupper($report->ai_severity ?? 'M'), 0, 3) }}</span>
                                    @if($report->ai_red_flag)
                                        <span class="text-xs text-rose-950 bg-white rounded-full px-1.5 mt-0.5 font-extrabold shadow">!</span>
                                    @endif
                                </div>
                                <span class="text-xs text-slate-400 mt-1 font-medium">
                                    {{ \Carbon\Carbon::parse($report->reported_at)->format('d/m') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Recent Reports (Top 3) -->
                    <div class="space-y-2 mt-3 max-h-44 overflow-y-auto pr-1">
                        @foreach($recent_reports->take(3) as $report)
                            <div class="p-2.5 rounded-xl bg-slate-900/80 border border-white/[0.06] text-xs">
                                <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                                    <span class="font-medium text-slate-300">{{ \Carbon\Carbon::parse($report->reported_at)->format('M d, H:i') }}</span>
                                    <div class="flex items-center gap-1.5">
                                        @if($report->ai_safety_override)
                                            <span class="text-amber-400 font-semibold">{{ __('app.safety_override_active') }}</span>
                                        @endif
                                        <span class="font-bold text-slate-200 uppercase px-1.5 py-0.5 rounded bg-slate-800 border border-slate-700/60">{{ $report->ai_severity }}</span>
                                    </div>
                                </div>
                                <p class="text-slate-300 italic">"{{ $report->report_text }}"</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500 py-6 text-center">{{ __('app.no_reports_yet') }}</p>
                @endif
            </div>
        </div>

        <!-- 4B. Active Restrictions -->
        <div class="soft-card rounded-2xl p-5 shadow-xl flex flex-col justify-between">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-3 flex items-center gap-2">
                    <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    {{ __('app.active_restrictions') }}
                </h2>
                @if(count($active_restrictions) > 0)
                    <div class="space-y-2">
                        @foreach($active_restrictions as $restrictionCode)
                            <div class="flex items-center gap-2.5 rounded-xl border border-amber-500/30 bg-gradient-to-r from-amber-950/30 to-slate-900/40 px-3.5 py-2.5 text-xs sm:text-sm text-amber-200">
                                <svg class="h-4 w-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span class="font-medium">{{ __('app.restriction_' . $restrictionCode) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center gap-2.5 rounded-xl border border-emerald-500/30 bg-gradient-to-r from-emerald-950/30 to-slate-900/40 px-3.5 py-4 text-xs sm:text-sm text-emerald-300">
                        <svg class="h-5 w-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium">{{ __('app.no_restrictions') }}</span>
                    </div>
                @endif
            </div>
            <p class="text-[11px] text-slate-500 mt-3 italic">Clinical guidance strictly adheres to CDC Return to Learn protocols.</p>
        </div>

        <!-- 4C. AI Layperson Insight & Safe Summary (Special AI Aura Card) -->
        <div class="ai-card rounded-2xl p-5 shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-teal-300 flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-400"></span>
                        </span>
                        {{ __('app.ai_insight') }}
                    </h2>
                    <span class="text-[10px] font-mono uppercase tracking-wider px-2 py-0.5 rounded-full bg-teal-500/10 text-teal-300 border border-teal-500/30">
                        AI Assistant
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-200 leading-relaxed font-normal">
                    {{ $ai_insight }}
                </p>
            </div>

            <!-- Mandatory Assistive Disclaimer -->
            @include('partials.ai-disclaimer')
        </div>

    </div>

    <!-- 5. Real Patient-Facing Approvals Status Card (Consensus Clearances) -->
    <div class="soft-card rounded-2xl p-5 sm:p-6 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-200 flex items-center gap-2">
                    <svg class="h-4 w-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    {{ __('app.approval_for_stage', ['stage' => min(4, $current_stage + 1)]) }}
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ __('app.patient_status_only_notice') }}
                </p>
            </div>
            @if($all_approvals_ready)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-950 px-3 py-1 text-xs font-bold text-emerald-300 border border-emerald-500/50 shadow-sm shadow-emerald-500/20">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Milestone Advancement Ready
                </span>
            @endif
        </div>

        @if(count($approval_statuses) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($approval_statuses as $approval)
                    @php
                        $statusBadge = match($approval['status']) {
                            'approved' => 'bg-emerald-950/80 text-emerald-300 border-emerald-500/50',
                            'rejected' => 'bg-rose-950/80 text-rose-300 border-rose-500/50',
                            default => 'bg-amber-950/80 text-amber-300 border-amber-500/50',
                        };

                        $roleIcon = match($approval['role']) {
                            'doctor' => '<svg class="h-4 w-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
                            'school' => '<svg class="h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>',
                            default => '<svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                        };
                    @endphp
                    <div class="rounded-xl border border-white/[0.08] bg-slate-900/60 p-4 hover:border-white/[0.14] transition">
                        <div class="flex items-center justify-between mb-2.5">
                            <div class="flex items-center gap-2">
                                <span class="p-1 rounded-lg bg-slate-800/80 shrink-0">{!! $roleIcon !!}</span>
                                <span class="text-xs sm:text-sm font-bold text-slate-200">{{ __('app.role_' . $approval['role']) }}</span>
                            </div>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold border {{ $statusBadge }}">
                                {{ __('app.status_' . $approval['status']) }}
                            </span>
                        </div>
                        <div class="text-xs sm:text-sm text-slate-400">
                            Approver: <strong class="text-slate-200">{{ $approval['name'] }}</strong>
                        </div>
                        @if($approval['decided_at'])
                            <div class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                                <svg class="h-3 w-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ __('app.timestamp') }}: {{ \Carbon\Carbon::parse($approval['decided_at'])->format('M d, H:i') }}
                            </div>
                        @endif
                        @if(!empty($approval['comments']))
                            <div class="mt-3 rounded-lg border {{ $approval['status'] === 'rejected' ? 'border-rose-500/30 bg-rose-950/30 text-rose-200' : 'border-slate-800 bg-slate-950/50 text-slate-300' }} p-2.5 text-xs">
                                <div class="font-semibold text-[11px] {{ $approval['status'] === 'rejected' ? 'text-rose-300' : 'text-slate-400' }} mb-1 flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                    </svg>
                                    <span>{{ __('app.decision_notes') }}</span>
                                </div>
                                <p class="italic leading-relaxed">"{{ $approval['comments'] }}"</p>
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
                    @php
                        // Check if this link is gated by Stage 4 Step 6 prerequisite
                        $isStage4Gated = (min(4, $current_stage + 1) === 4 && ($patient->activity_step ?? 0) < 6);
                    @endphp
                    <div class="rounded-xl border {{ $isStage4Gated ? 'border-amber-500/40 bg-slate-900/90' : 'border-indigo-500/20 bg-slate-900/80' }} p-3.5 flex flex-col justify-between">
                        <div class="mb-3">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold {{ $isStage4Gated ? 'text-amber-300' : 'text-indigo-300' }}">{{ __('app.role_' . $approval['role']) }}</span>
                                @if($isStage4Gated)
                                    <span class="inline-flex items-center gap-1 text-[11px] text-amber-300 font-bold bg-amber-950/80 px-2 py-0.5 rounded border border-amber-500/40">
                                        <svg class="h-3 w-3 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Locked: Step 6 Req.
                                    </span>
                                @else
                                    <span class="text-xs text-amber-400 font-semibold">{{ __('app.status_pending') }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-300 truncate" title="{{ $approval['name'] }}">
                                {{ $approval['name'] }}
                            </div>
                            @if($isStage4Gated)
                                <p class="text-[11px] text-amber-300/90 mt-2 bg-amber-950/40 p-2 rounded-lg border border-amber-500/25 leading-relaxed">
                                    🔒 <strong>CDC Protocol Gate:</strong> Final clearance is locked until Step 6 is finished (Current: Step {{ $patient->activity_step ?? 1 }}). Clicking below tests the 403 safety gate.
                                </p>
                            @endif
                        </div>

                        <a href="{{ $approval['link_url'] }}" target="_blank"
                           class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg {{ $isStage4Gated ? 'bg-amber-600/80 hover:bg-amber-600 text-white font-bold' : 'bg-indigo-600 hover:bg-indigo-500 text-white font-bold' }} px-3 py-2 text-xs shadow-md transition">
                            <span>{{ $isStage4Gated ? 'Test Safety Gate (403)' : __('app.open_link') }}</span>
                            <svg class="h-3.5 w-3.5 {{ $isStage4Gated ? 'text-amber-200' : 'text-indigo-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

<!-- Symptom Logging Modal with Loading Spinner -->
<div id="symptomModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md">
    <div class="soft-card rounded-2xl p-6 max-w-lg w-full shadow-2xl border border-slate-700 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/[0.08]">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <span class="p-1 rounded-lg bg-teal-500/20 text-teal-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
                {{ __('app.log_daily_symptoms') }}
            </h3>
            <button onclick="document.getElementById('symptomModal').classList.add('hidden')" class="text-slate-400 hover:text-white text-lg font-bold p-1 rounded-lg hover:bg-slate-800 transition">
                &times;
            </button>
        </div>

        <form id="symptomForm" method="POST" action="{{ route('symptom_reports.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="patient_id" value="{{ $patient->id }}">

            <div>
                <label for="report_text" class="block text-xs font-semibold text-slate-200 mb-1.5">
                    Daily Symptom Observations (Free-Text)
                </label>
                <textarea id="report_text" name="report_text" rows="4" required
                          placeholder="{{ __('app.enter_symptoms_placeholder') }}"
                          class="w-full rounded-xl border border-slate-700 bg-slate-900 px-3.5 py-2.5 text-xs sm:text-sm text-slate-100 placeholder-slate-500 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition"></textarea>
            </div>

            <!-- Quick Template Prompt Helper Chips -->
            <div class="space-y-1.5">
                <span class="text-xs font-semibold text-slate-400 block">Quick suggestions:</span>
                <div class="flex flex-wrap gap-1.5 text-xs">
                    <button type="button" onclick="document.getElementById('report_text').value = 'Feeling fine today. Walked for 20 minutes with zero headache, dizziness, or visual fatigue.'" 
                            class="inline-flex items-center gap-1.5 text-xs text-teal-300 hover:text-teal-200 bg-teal-950/50 hover:bg-teal-950/80 px-2.5 py-1 rounded-lg border border-teal-500/30 hover:border-teal-500/60 transition shadow-sm">
                        <span class="h-1.5 w-1.5 rounded-full bg-teal-400"></span>
                        Mild / Improving
                    </button>
                    <button type="button" onclick="document.getElementById('report_text').value = 'Had mild headache after 30 minutes of homework, subsided after 10 minutes of rest. No nausea.'" 
                            class="inline-flex items-center gap-1.5 text-xs text-amber-300 hover:text-amber-200 bg-amber-950/50 hover:bg-amber-950/80 px-2.5 py-1 rounded-lg border border-amber-500/30 hover:border-amber-500/60 transition shadow-sm">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                        Moderate
                    </button>
                    <button type="button" onclick="document.getElementById('report_text').value = 'Severe headache suddenly worsened, repeated vomiting twice this morning, feeling very confused.'" 
                            class="inline-flex items-center gap-1.5 text-xs text-rose-300 hover:text-rose-200 bg-rose-950/50 hover:bg-rose-950/80 px-2.5 py-1 rounded-lg border border-rose-500/30 hover:border-rose-500/60 transition shadow-sm">
                        <span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span>
                        Red Flag / Danger
                    </button>
                </div>
            </div>

            <!-- Loading status indicator -->
            <div id="aiEvaluatingNotice" class="hidden text-xs text-teal-300 flex items-center gap-2 p-2 rounded-lg bg-teal-950/40 border border-teal-500/30 animate-pulse">
                <svg class="animate-spin h-3.5 w-3.5 text-teal-400 shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ __('app.analyzing_symptoms') }}</span>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('symptomModal').classList.add('hidden')" 
                        class="rounded-xl border border-slate-700 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                    Cancel
                </button>
                <button type="submit" id="submitSymptomBtn" 
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-500 hover:bg-teal-400 px-5 py-2 text-xs font-bold text-slate-950 shadow-md transition disabled:opacity-75 disabled:cursor-not-allowed">
                    <svg id="submitSymptomSpinner" class="hidden animate-spin h-3.5 w-3.5 text-slate-950" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="submitSymptomText">{{ __('app.analyze_and_submit') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('symptomForm')?.addEventListener('submit', function() {
        const btn = document.getElementById('submitSymptomBtn');
        const spinner = document.getElementById('submitSymptomSpinner');
        const text = document.getElementById('submitSymptomText');
        const notice = document.getElementById('aiEvaluatingNotice');
        if (btn) {
            btn.disabled = true;
            spinner?.classList.remove('hidden');
            notice?.classList.remove('hidden');
            if (text) text.textContent = '{{ __('app.analyzing_symptoms') }}';
        }
    });
</script>
@endsection
