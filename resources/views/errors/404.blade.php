@extends('layouts.app')

@section('title', '404 — Not Found')

@section('content')
<div class="max-w-md mx-auto py-16 text-center">
    <div class="soft-card rounded-2xl p-8 border border-slate-800 shadow-2xl">
        <div class="mx-auto mb-4 h-12 w-12 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 flex items-center justify-center font-bold text-lg">
            404
        </div>
        <h1 class="text-lg font-bold text-white mb-2">Page or Link Not Found</h1>
        <p class="text-xs text-slate-400 mb-6 leading-relaxed">
            {{ $exception->getMessage() ?: __('app.approval_token_invalid_or_used') }}
        </p>
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-teal-500 hover:bg-teal-400 px-4 py-2 text-xs font-bold text-slate-950 transition shadow">
            Return to Sign In
        </a>
    </div>
</div>
@endsection
