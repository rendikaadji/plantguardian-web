<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsRanger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->role !== 'ranger') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Akses ditolak. Halaman atau endpoint ini khusus untuk pengguna ber-role Ranger.',
                ], 403);
            }

            return redirect()->route('home')->with('error', 'Akses khusus Ranger.');
        }

        return $next($request);
    }
}
