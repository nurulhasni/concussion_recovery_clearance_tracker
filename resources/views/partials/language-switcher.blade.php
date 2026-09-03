<div class="inline-flex items-center rounded-lg border border-slate-700 bg-slate-800/80 p-0.5 sm:p-1 text-xs text-slate-300 shadow-sm backdrop-blur">
    <a href="{{ route('lang.switch', 'en') }}" 
       class="rounded px-2 sm:px-2.5 py-0.5 sm:py-1 text-[11px] sm:text-xs font-medium transition {{ app()->getLocale() === 'en' ? 'bg-teal-500 text-slate-950 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-700/50' }}">
        EN
    </a>
    <a href="{{ route('lang.switch', 'id') }}" 
       class="rounded px-2 sm:px-2.5 py-0.5 sm:py-1 text-[11px] sm:text-xs font-medium transition {{ app()->getLocale() === 'id' ? 'bg-teal-500 text-slate-950 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-700/50' }}">
        ID
    </a>
</div>
