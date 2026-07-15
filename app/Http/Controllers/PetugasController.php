<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PetugasController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $role   = $request->get('role');
        $status = $request->get('status'); // 'aktif' | 'non_aktif' | null

        $query = User::query()
            ->when($search, fn($q) => $q->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($role, fn($q) => $q->where('role', $role))
            ->when($status === 'aktif',     fn($q) => $q->where('is_active', true))
            ->when($status === 'non_aktif', fn($q) => $q->where('is_active', false))
            ->orderByRaw("FIELD(role,'admin','petugas')")
            ->orderBy('name');

        $users    = $query->paginate(15)->withQueryString();
        $allUsers = User::selectRaw("COUNT(*) as total, SUM(role='admin') as admin, SUM(role='petugas') as petugas")->first();

        return view('sips.petugas.index', compact('users', 'allUsers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'username'              => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,username'],
            'email'                 => ['required', 'email', 'max:100', 'unique:users,email'],
            'role'                  => ['required', 'in:admin,petugas'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ], [
            'username.regex'    => 'Username hanya boleh huruf, angka, titik, garis bawah, dan strip.',
            'username.unique'   => 'Username sudah dipakai akun lain.',
            'email.unique'      => 'Email sudah dipakai akun lain.',
            'password.min'      => 'Password minimal 8 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'role'     => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('sips.petugas.index')
            ->with('success', "Akun \"{$user->name}\" berhasil dibuat.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name'                  => ['required', 'string', 'max:100'],
            'username'              => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/', "unique:users,username,{$user->id}"],
            'email'                 => ['required', 'email', 'max:100', "unique:users,email,{$user->id}"],
            'role'                  => ['required', 'in:admin,petugas'],
            'password'              => ['nullable', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['nullable'],
        ], [
            'username.regex'    => 'Username hanya boleh huruf, angka, titik, garis bawah, dan strip.',
            'username.unique'   => 'Username sudah dipakai akun lain.',
            'email.unique'      => 'Email sudah dipakai akun lain.',
            'password.min'      => 'Password minimal 8 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('_edit_id', $user->id);
        }

        $validated = $validator->validated();

        if ($user->id === auth()->id() && $validated['role'] !== $user->role) {
            return back()->withErrors(['role' => 'Tidak dapat mengubah role akun sendiri yang sedang login.'])
                ->withInput()->with('_edit_id', $user->id);
        }

        if ($user->role === 'admin' && $validated['role'] === 'petugas') {
            if (User::where('role', 'admin')->count() <= 1) {
                return back()->withErrors(['role' => 'Tidak dapat mengubah role — harus ada minimal 1 admin aktif.'])
                    ->withInput()->with('_edit_id', $user->id);
            }
        }

        $data = [
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'role'     => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('sips.petugas.index')
            ->with('success', "Akun \"{$user->name}\" berhasil diperbarui.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat mengubah status akun yang sedang digunakan.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun \"{$user->name}\" berhasil {$status}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun yang sedang digunakan.');
        }

        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus satu-satunya admin yang tersisa.');
        }

        $nama = $user->name;
        $user->delete();

        return redirect()->route('sips.petugas.index')
            ->with('success', "Akun \"{$nama}\" berhasil dihapus.");
    }
}
