<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('app.app_name')) — Concussion Recovery Tracker</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vite Assets (Tailwind CSS v4 & JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col text-slate-100 selection:bg-teal-500 selection:text-slate-950 antialiased">

    <!-- Glowing Top Accent Line -->
    <div class="h-[2px] w-full bg-gradient-to-r from-transparent via-teal-500/70 to-transparent"></div>

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 border-b border-white/[0.08] bg-slate-950/80 backdrop-blur-xl shadow-lg shadow-black/20">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-14 sm:h-16 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <div class="h-8 w-8 sm:h-9 sm:w-9 rounded-xl bg-gradient-to-tr from-teal-500 to-cyan-400 p-0.5 shadow-lg shadow-teal-500/20 ring-1 ring-white/20 flex items-center justify-center shrink-0">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <a href="{{ url('/') }}" class="text-sm sm:text-base font-bold tracking-tight text-white hover:text-teal-300 transition truncate flex items-center gap-1.5" title="{{ __('app.app_name') }}">
                        <span class="sm:hidden">Recovery</span>
                        <span class="hidden sm:inline">{{ __('app.app_name') }}</span>
                    </a>
                    <span class="hidden sm:inline-block text-[11px] text-teal-400/90 font-medium truncate">
                        CDC HEADS UP & PedsConcussion Framework
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                <!-- Platform & Demo Guide Modal Trigger -->
                <button type="button" 
                        onclick="document.getElementById('guideModal').classList.remove('hidden')"
                        class="inline-flex items-center justify-center gap-1.5 rounded-full bg-teal-500/10 hover:bg-teal-500/20 p-2 sm:px-3 sm:py-1 text-xs font-semibold text-teal-300 border border-teal-500/30 hover:border-teal-500/60 transition shadow-sm shrink-0"
                        title="Platform Features & 3-Minute Demo Cheat Sheet">
                    <svg class="h-4 w-4 text-teal-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="hidden sm:inline">Guide & Demo Flow</span>
                </button>

                <span class="hidden md:inline-flex items-center rounded-full bg-slate-900/90 px-2.5 py-0.5 text-xs font-semibold text-slate-300 border border-slate-700/80 shadow-sm">
                    {{ __('app.demo_mode') }}
                </span>

                <!-- Language Switcher -->
                @include('partials.language-switcher')

                @if(session('authenticated_patient_id'))
                    <div class="flex items-center gap-1.5 sm:gap-2 pl-1.5 sm:pl-2 border-l border-slate-800">
                        <span class="hidden lg:inline text-xs text-slate-400">
                            {{ __('app.logged_in_as') }} <strong class="text-slate-200">{{ session('authenticated_patient_name') }}</strong>
                        </span>
                        <a href="{{ route('logout') }}" 
                           class="inline-flex items-center gap-1 text-xs text-rose-400 hover:text-rose-300 font-medium px-2 py-1 rounded-lg hover:bg-rose-950/40 transition"
                           title="{{ __('app.logout') }}">
                            <svg class="h-4 w-4 sm:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span class="hidden sm:inline">{{ __('app.logout') }}</span>
                        </a>
                    </div>
                @elseif(!request()->routeIs('login', 'approval.*'))
                    <a href="{{ route('login') }}" class="text-xs text-teal-300 hover:text-teal-200 font-semibold px-2.5 sm:px-3 py-1.5 rounded-lg border border-teal-500/40 hover:bg-teal-950/40 transition shadow-sm">
                        {{ __('app.login') }}
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <!-- Flash & Alert Messages -->
        @if(session('success'))
            <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-950/40 p-4 text-sm text-emerald-300 flex items-start gap-3 shadow-lg shadow-emerald-950/20">
                <svg class="h-5 w-5 text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-6 rounded-xl border border-amber-500/40 bg-amber-950/40 p-4 text-sm text-amber-300 flex items-start gap-3 shadow-lg shadow-amber-950/20">
                <svg class="h-5 w-5 text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    {{ session('warning') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-xl border border-rose-500/40 bg-rose-950/40 p-4 text-sm text-rose-300 flex items-start gap-3 shadow-lg shadow-rose-950/20">
                <svg class="h-5 w-5 text-rose-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-slate-800/80 bg-slate-950/90 py-6 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
            <div>
                <p class="font-medium text-slate-400">{{ __('app.app_name') }} &mdash; {{ __('app.tagline') }}</p>
                <p class="mt-1 text-slate-600">Clinical references: CDC HEADS UP Returning to School & 6-Step Return to Play Progression &middot; PedsConcussion Living Guidelines</p>
            </div>
            <div class="text-slate-500">
                <span>{{ __('app.demo_mode') }}</span>
            </div>
        </div>
    </footer>

    <!-- Platform & Demo Guide Modal -->
    @include('partials.guide-modal')

    @stack('scripts')
</body>
</html>
