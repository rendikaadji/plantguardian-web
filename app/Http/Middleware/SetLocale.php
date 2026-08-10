<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'id';

        if ($request->hasSession() && session()->has('locale') && in_array(session('locale'), ['en', 'id'])) {
            $locale = session('locale');
        } elseif (Auth::check() && Auth::user()->locale && in_array(Auth::user()->locale, ['en', 'id'])) {
            $locale = Auth::user()->locale;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
