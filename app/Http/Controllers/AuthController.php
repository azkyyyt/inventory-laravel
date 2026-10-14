<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Session::has('user')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::with('profile')
            ->where('username', $request->username)
            ->first();

        if ($user && password_verify($request->password, $user->password_hash)) {
            Session::regenerate();
            Session::put('user', [
                'id'        => $user->id,
                'username'  => $user->username,
                'role'      => $user->role,
                'full_name' => $user->profile->full_name ?? $user->username,
            ]);
            return redirect()->route('dashboard')
                ->with('flash_success', 'Selamat datang kembali, ' . (($user->profile->full_name ?? $user->username)) . '!');
        }

        return back()->withInput(['username' => $request->username])
            ->with('error', 'Username atau password tidak sesuai.');
    }

    public function logout()
    {
        Session::flush();
        return redirect()->route('login');
    }
}
