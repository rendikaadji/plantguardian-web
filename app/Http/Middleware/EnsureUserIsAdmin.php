<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     * Ensure authenticated user is an Admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akses khusus Admin ditolak.',
                ], 403);
            }

            abort(403, 'Akses terbatas hanya untuk role Admin.');
        }

        return $next($request);
    }
}
