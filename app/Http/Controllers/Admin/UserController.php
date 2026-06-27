<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barbershop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        // Hanya super admin yang boleh akses — sudah dijamin via route middleware
    }

    public function index()
    {
        $users = User::with('branch.barbershop')
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $branches = \App\Models\Branch::with('barbershop')->orderBy('name')->get();
        return view('admin.users.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:6|confirmed',
            'role'           => 'required|in:super_admin,admin',
            'branch_id'      => 'nullable|exists:branches,id',
        ]);

        // Admin harus punya branch
        if ($data['role'] === 'admin' && empty($data['branch_id'])) {
            return back()->withErrors(['branch_id' => 'Admin harus di-assign ke cabang.'])->withInput();
        }

        // Super admin tidak perlu branch
        if ($data['role'] === 'super_admin') {
            $data['branch_id'] = null;
        }

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$data['name']}' berhasil ditambahkan!");
    }

    public function edit(User $user)
    {
        $branches = \App\Models\Branch::with('barbershop')->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'password'       => 'nullable|string|min:6|confirmed',
            'role'           => 'required|in:super_admin,admin',
            'branch_id'      => 'nullable|exists:branches,id',
        ]);

        if ($data['role'] === 'admin' && empty($data['branch_id'])) {
            return back()->withErrors(['branch_id' => 'Admin harus di-assign ke cabang.'])->withInput();
        }

        if ($data['role'] === 'super_admin') {
            $data['branch_id'] = null;
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' berhasil diperbarui!");
    }

    public function destroy(User $user)
    {
        // Proteksi: tidak bisa hapus diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$name}' berhasil dihapus.");
    }
}
