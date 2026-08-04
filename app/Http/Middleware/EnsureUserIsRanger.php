<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsRanger
{
    /**
     * Handle an incoming request.
     * Allows both Ranger and Admin roles to access Ranger resources.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['ranger', 'admin'])) {
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
