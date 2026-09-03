@extends('layouts.app')

@section('title', __('app.approval_confirmed_title'))

@section('content')
<div class="max-w-lg mx-auto py-12">
    <div class="soft-card rounded-2xl p-8 shadow-2xl border border-slate-800 text-center">
        
        <div class="mx-auto mb-4 h-16 w-16 rounded-2xl {{ $decision === 'approved' ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' : 'bg-rose-500/10 border border-rose-500/30 text-rose-400' }} flex items-center justify-center shadow-inner">
            @if($decision === 'approved')
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            @else
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            @endif
        </div>

        <h1 class="text-xl font-bold tracking-tight text-white mb-2">
            {{ __('app.approval_confirmed_title') }}
        </h1>
        
        <p class="text-xs sm:text-sm text-slate-400 mb-6 leading-relaxed">
            {{ __('app.approval_confirmed_desc', ['decision' => strtoupper($decision)]) }}
            for patient <strong class="text-slate-200">{{ $patient->name }}</strong>.
        </p>

        @if($milestoneAdvanced)
            <div class="mb-6 rounded-xl border border-emerald-500/40 bg-emerald-950/40 p-4 text-xs text-emerald-300">
                <div class="font-bold text-sm text-emerald-200 mb-1 flex items-center justify-center gap-1.5">
                    🎉 {{ __('app.milestone_unlocked_notice', ['stage' => $targetStage]) }}
                </div>
                <p class="text-emerald-400/90">
                    All required multidisciplinary approvers have confirmed their sign-off.
                </p>
            </div>
        @else
            <div class="mb-6 rounded-xl border border-slate-800 bg-slate-900/60 p-3 text-xs text-slate-400">
                Awaiting remaining required approvers or holding at current stage based on clinical review.
            </div>
        @endif

        @if(!empty($comments))
            <div class="mb-6 rounded-xl border {{ $decision === 'rejected' ? 'border-rose-500/30 bg-rose-950/30 text-rose-200' : 'border-slate-800 bg-slate-900/60 text-slate-300' }} p-3.5 text-xs text-left">
                <div class="font-semibold text-xs {{ $decision === 'rejected' ? 'text-rose-300' : 'text-slate-400' }} mb-1 flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <span>{{ __('app.decision_notes') }}</span>
                </div>
                <p class="italic leading-relaxed">"{{ $comments }}"</p>
            </div>
        @endif

        <div class="pt-4 border-t border-slate-800">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 hover:bg-slate-700 px-5 py-2.5 text-xs font-bold text-slate-200 transition border border-slate-700">
                Return to Sign In
            </a>
        </div>

    </div>
</div>
@endsection
