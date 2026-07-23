<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ], [
            'login.required' => 'Nama pengguna atau email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = (bool) ($validated['remember'] ?? false);
        $login = $validated['login'];

        $credentials = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? ['email' => $login, 'password' => $validated['password']]
            : ['username' => $login, 'password' => $validated['password']];

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'login' => 'Kredensial yang Anda masukkan tidak valid.',
            ]);
        }

        // Block non-active accounts immediately after credential check
        if (! Auth::user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'login' => 'Akun Anda telah dinonaktifkan. Hubungi admin untuk mengaktifkan kembali.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('sips.dashboard'))
            ->with('success', 'Berhasil masuk ke dashboard.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah keluar dari sistem.');
    }
}
