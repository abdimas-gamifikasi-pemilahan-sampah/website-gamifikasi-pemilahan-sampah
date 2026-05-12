<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                abort(403, 'Anda tidak memiliki akses ke fitur ini.');
            }

            return redirect()
                ->route('sips.dashboard')
                ->with('error', 'Akun Anda tidak memiliki akses ke fitur tersebut.');
        }

        return $next($request);
    }
}
