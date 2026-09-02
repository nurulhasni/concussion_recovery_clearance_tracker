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
<body class="min-h-full flex flex-col bg-slate-950 text-slate-100 selection:bg-teal-500 selection:text-slate-950">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 border-b border-slate-800/80 bg-slate-950/85 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-gradient-to-tr from-teal-600 to-cyan-400 p-0.5 shadow-lg shadow-teal-900/30 flex items-center justify-center">
                    <svg class="h-5 w-5 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <a href="{{ url('/') }}" class="text-base font-bold tracking-tight text-white flex items-center gap-2">
                        {{ __('app.app_name') }}
                    </a>
                    <span class="hidden sm:inline-block text-xs text-teal-400/90 font-medium">
                        CDC HEADS UP & PedsConcussion Framework
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden md:inline-flex items-center rounded-full bg-slate-800/90 px-2.5 py-0.5 text-xs font-medium text-slate-400 border border-slate-700/60">
                    {{ __('app.demo_mode') }}
                </span>

                <!-- Language Switcher -->
                @include('partials.language-switcher')

                @if(session('authenticated_patient_id'))
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-800">
                        <span class="hidden lg:inline text-xs text-slate-400">
                            {{ __('app.logged_in_as') }} <strong class="text-slate-200">{{ session('authenticated_patient_name') }}</strong>
                        </span>
                        <a href="{{ route('logout') }}" class="text-xs text-rose-400 hover:text-rose-300 font-medium px-2 py-1 rounded hover:bg-rose-950/30 transition">
                            {{ __('app.logout') }}
                        </a>
                    </div>
                @elseif(!request()->routeIs('login'))
                    <a href="{{ route('login') }}" class="text-xs text-teal-400 hover:text-teal-300 font-medium px-3 py-1.5 rounded-lg border border-teal-500/30 hover:bg-teal-950/30 transition">
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

    @stack('scripts')
</body>
</html>
