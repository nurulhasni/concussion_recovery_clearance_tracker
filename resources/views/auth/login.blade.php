@extends('layouts.app')

@section('title', __('app.auth_title'))

@section('content')
<div class="max-w-md mx-auto py-8">
    <div class="soft-card rounded-2xl p-6 sm:p-8 shadow-2xl border border-slate-800">
        
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="mx-auto mb-3 h-12 w-12 rounded-2xl bg-teal-500/10 border border-teal-500/30 flex items-center justify-center text-teal-400 shadow-inner">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-white">{{ __('app.auth_title') }}</h1>
            <p class="mt-1.5 text-xs text-slate-400 leading-relaxed">{{ __('app.auth_subtitle') }}</p>
        </div>

        @if(session('demo_magic_link'))
            <!-- Generated Demo Magic Link View (Only visible state after generation) -->
            <div id="demoSuccessCard" class="space-y-4">
                <div class="rounded-xl border border-teal-500/40 bg-teal-950/50 p-5 text-xs text-teal-200 shadow-lg">
                    <div class="flex items-center gap-2 mb-2 font-semibold text-teal-300">
                        <svg class="h-4 w-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>{{ __('app.magic_link_sent_message') }}</span>
                    </div>
                    <p class="mb-4 text-slate-300 text-sm">
                        Target Patient: <strong class="text-white">{{ session('demo_patient_name') }}</strong>
                    </p>
                    <a href="{{ session('demo_magic_link') }}" class="block text-center w-full py-3 px-4 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold shadow-md hover:shadow-teal-900/50 transition">
                        👉 Click to Enter Recovery Passport
                    </a>
                </div>

                <!-- Secondary reset button to generate a different link -->
                <div class="text-center pt-2">
                    <button type="button" 
                            onclick="document.getElementById('demoSuccessCard').classList.add('hidden'); document.getElementById('loginFormSection').classList.remove('hidden');" 
                            class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-teal-300 transition py-1 px-2 rounded-lg hover:bg-slate-900">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ __('app.generate_different_link') }}
                    </button>
                </div>
            </div>
        @endif

        <!-- Email Input Form & Quick Select List (Hidden when magic link was just generated) -->
        <div id="loginFormSection" class="{{ session('demo_magic_link') ? 'hidden' : '' }}">
            <form method="POST" action="{{ route('auth.magic_link.send') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">{{ __('app.email_label') }}</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="parent@example.com"
                           class="w-full rounded-xl border border-slate-700 bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full rounded-xl bg-teal-600 hover:bg-teal-500 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-teal-900/30 transition flex items-center justify-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    {{ __('app.send_magic_link') }}
                </button>
            </form>

            <!-- Quick Select Demo Patients -->
            @if(isset($demoPatients) && $demoPatients->count() > 0)
                <div class="mt-8 pt-6 border-t border-slate-800">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 text-center">
                        {{ __('app.quick_demo_accounts') }}
                    </p>
                    <div class="space-y-2">
                        @foreach($demoPatients as $patient)
                            <form method="POST" action="{{ route('auth.magic_link.send') }}">
                                @csrf
                                <input type="hidden" name="email" value="{{ $patient->parent_email }}">
                                <button type="submit" class="w-full text-left p-3 rounded-xl bg-slate-900/60 hover:bg-slate-800/80 border border-slate-800 hover:border-slate-700 transition flex items-center justify-between group">
                                    <div>
                                        <div class="text-xs font-semibold text-slate-200 group-hover:text-teal-300 transition">
                                            {{ $patient->name }}
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            {{ $patient->parent_email }} &middot; 
                                            <span class="text-teal-400 font-medium">Milestone {{ $patient->current_stage }}</span>
                                            @if($patient->current_stage === 3)
                                                <span class="text-amber-400 font-medium">(Step {{ $patient->activity_step }}/6)</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold text-teal-400 opacity-80 group-hover:opacity-100 group-hover:translate-x-0.5 transition">
                                        Login &rarr;
                                    </span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
