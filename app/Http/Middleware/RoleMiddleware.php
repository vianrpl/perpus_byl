<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Normalisasi role jadi lowercase & tanpa spasi
        $userRole = strtolower(trim($user->role));
        $roles = array_map(fn($r) => strtolower(trim($r)), $roles);

        if (!in_array($user->role, $roles)) {
            abort(403, "Unauthorized. User role: {$user->role}, Allowed: " . implode(',', $roles));
        }


        return $next($request);
    }
}
