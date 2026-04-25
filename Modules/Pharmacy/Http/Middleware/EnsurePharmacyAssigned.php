<?php

namespace Modules\Pharmacy\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePharmacyAssigned
{
    /**
     * Ensure the authenticated pharmacy-role user has a pharmacy_id assigned.
     * Without this, all panel queries would use WHERE pharmacy_id = NULL,
     * returning empty results silently instead of showing a clear error.
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && ! auth()->user()->pharmacy_id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No pharmacy assigned to your account. Please contact an administrator.',
                ], 403);
            }

            abort(403, 'No pharmacy assigned to your account. Please contact an administrator.');
        }

        return $next($request);
    }
}
