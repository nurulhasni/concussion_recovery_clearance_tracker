<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = ['en', 'id'];
        $locale = session('locale', config('app.locale', 'en'));

        if (! in_array($locale, $allowed, true)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
