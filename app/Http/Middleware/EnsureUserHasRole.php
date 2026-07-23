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

        // Block non-active accounts from accessing any protected page
        if (! $user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                abort(403, 'Akun Anda telah dinonaktifkan.');
            }

            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Hubungi admin untuk mengaktifkan kembali.');
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
