<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserProfile;

class UserController extends Controller
{
    public function index()
    {
        $usersList = User::with('profile')->orderBy('id')->get();
        return view('users.index', compact('usersList'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username'  => 'required|unique:users,username',
            'password'  => 'required|min:6',
            'full_name' => 'required|string|max:100',
            'phone'     => 'nullable|string|max:20',
            'role'      => 'required|in:admin,staff',
        ], [
            'username.required'  => 'Username wajib diisi.',
            'username.unique'    => 'Username sudah digunakan.',
            'password.required'  => 'Password wajib diisi minimal 6 karakter.',
            'password.min'       => 'Password wajib diisi minimal 6 karakter.',
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'role.required'      => 'Role wajib dipilih.',
            'role.in'            => 'Role tidak valid.',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'username'      => $request->username,
                'password_hash' => password_hash($request->password, PASSWORD_DEFAULT),
                'role'          => $request->role,
            ]);

            UserProfile::create([
                'user_id'   => $user->id,
                'full_name' => $request->full_name,
                'phone'     => $request->phone,
            ]);
        });

        return redirect()->route('users.index')
            ->with('flash_success', 'Pengguna "' . $request->username . '" berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $user->load('profile');
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'username'  => 'required|unique:users,username,' . $user->id,
            'full_name' => 'required|string|max:100',
            'phone'     => 'nullable|string|max:20',
            'role'      => 'required|in:admin,staff',
        ];
        $messages = [
            'username.required'  => 'Username wajib diisi.',
            'username.unique'    => 'Username sudah digunakan.',
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'role.required'      => 'Role wajib dipilih.',
            'role.in'            => 'Role tidak valid.',
        ];

        if ($request->filled('password')) {
            $rules['password']              = 'min:6';
            $messages['password.min']       = 'Password minimal 6 karakter.';
        }

        $request->validate($rules, $messages);

        DB::transaction(function () use ($request, $user) {
            $userData = [
                'username' => $request->username,
                'role'     => $request->role,
            ];
            if ($request->filled('password')) {
                $userData['password_hash'] = password_hash($request->password, PASSWORD_DEFAULT);
            }
            $user->update($userData);

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                ['full_name' => $request->full_name, 'phone' => $request->phone]
            );
        });

        return redirect()->route('users.index')
            ->with('flash_success', 'Data pengguna "' . $user->username . '" berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $currentUserId = session('user.id');
        if ($user->id === $currentUserId) {
            return redirect()->route('users.index')
                ->with('flash_error', 'Tidak dapat menghapus akun Anda sendiri.');
        }
        $username = $user->username;
        $user->delete();
        return redirect()->route('users.index')
            ->with('flash_success', 'Pengguna "' . $username . '" berhasil dihapus.');
    }
}
