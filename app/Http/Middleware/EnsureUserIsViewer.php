<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsViewer
{
    /**
     * Handle an incoming request (Allows both Viewer and Ranger roles for unified UI access).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! in_array($request->user()->role, ['viewer', 'ranger'])) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Akses ditolak. Endpoint ini memerlukan role terautentikasi.',
                ], 403);
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
