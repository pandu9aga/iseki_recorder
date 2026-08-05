<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = Admin::orderBy('username')->get();

        return view('admin.users.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:admins,username'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        Admin::create([
            'username' => $data['username'],
            'password' => $data['password'],
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User admin berhasil ditambahkan.');
    }

    public function edit(Admin $admin)
    {
        return view('admin.users.edit', compact('admin'));
    }

    public function update(Request $request, Admin $admin)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:admins,username,'.$admin->id],
            'password' => ['nullable', 'string', 'max:255'],
        ]);

        $admin->username = $data['username'];

        if (! empty($data['password'])) {
            $admin->password = $data['password'];
        }

        $admin->save();

        return redirect()->route('admin.users.index')->with('success', 'User admin berhasil diperbarui.');
    }

    public function destroy(Admin $admin)
    {
        if (session('admin_id') === $admin->id) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus akun yang sedang digunakan.']);
        }

        $admin->delete();

        return redirect()->route('admin.users.index')->with('success', 'User admin berhasil dihapus.');
    }
}
