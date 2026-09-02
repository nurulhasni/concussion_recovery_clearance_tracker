@extends('layouts.app')

@section('title', '403 — Unauthorized Access')

@section('content')
<div class="max-w-md mx-auto py-16 text-center">
    <div class="soft-card rounded-2xl p-8 border border-slate-800 shadow-2xl">
        <div class="mx-auto mb-4 h-12 w-12 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center justify-center font-bold text-lg">
            403
        </div>
        <h1 class="text-lg font-bold text-white mb-2">Access Restricted</h1>
        <p class="text-xs text-slate-400 mb-6 leading-relaxed">
            {{ $exception->getMessage() ?: __('app.unauthorized_patient_access') }}
        </p>
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-teal-500 hover:bg-teal-400 px-4 py-2 text-xs font-bold text-slate-950 transition shadow">
            Sign in with Authorized Account
        </a>
    </div>
</div>
@endsection
