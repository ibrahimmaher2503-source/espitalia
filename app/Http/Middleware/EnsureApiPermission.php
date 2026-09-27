<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureApiPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated'], 401);
        }

        if ($user->hasRole('admin')) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return $next($request);
            }
        }

        return response()->json(['status' => false, 'message' => __('messages.permission_denied')], 403);
    }
}
