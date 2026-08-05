<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('role') === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if (session('role') === 'member') {
            return redirect()->route('member.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'role' => ['required', 'in:admin,member'],
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($credentials['role'] === 'admin') {
            $admin = Admin::where('username', $credentials['login'])->first();

            if (! $admin || $admin->password !== $credentials['password']) {
                return back()->withInput()->withErrors(['login' => 'Username atau password admin salah.']);
            }

            $request->session()->regenerate();
            $request->session()->put([
                'role' => 'admin',
                'admin_id' => $admin->id,
                'name' => $admin->username,
            ]);

            return redirect()->route('admin.dashboard');
        }

        $nik = trim($credentials['login']);
        $employee = DB::connection('rifa')
            ->table('employees')
            ->where('nik', (int) $nik)
            ->whereNull('deleted_at')
            ->first();

        if (! $employee || (string) $employee->password !== $credentials['password']) {
            return back()->withInput()->withErrors(['login' => 'NIK atau password member salah.']);
        }

        $request->session()->regenerate();
        $request->session()->put([
            'role' => 'member',
            'nik' => (string) $employee->nik,
            'name' => $employee->nama,
        ]);

        return redirect()->route('member.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();

        return redirect()->route('login');
    }
}
