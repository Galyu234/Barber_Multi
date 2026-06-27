<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Barbershop;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterBarbershopController extends Controller
{
    public function showForm()
    {
        return view('auth.register-barbershop');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'barbershop_name' => 'required|string|max:100',
            'branch_name'     => 'required|string|max:100',
            'owner_name'      => 'required|string|max:100',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|string|min:8|confirmed',
            'address'         => 'nullable|string|max:300',
            'phone'           => 'nullable|string|max:20',
        ]);

        // Generate unique branch code: 2 huruf dari nama barbershop + 001
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $data['barbershop_name']), 0, 2));
        if (strlen($prefix) < 2) $prefix = str_pad($prefix, 2, 'X');

        // Cari suffix yang belum terpakai
        $suffix = 1;
        do {
            $code = $prefix . str_pad($suffix, 3, '0', STR_PAD_LEFT);
            $suffix++;
        } while (Branch::where('code', $code)->exists());

        DB::beginTransaction();
        try {
            // 1. Create Barbershop (tenant)
            $barbershop = Barbershop::create([
                'name'       => $data['barbershop_name'],
                'slug'       => Str::slug($data['barbershop_name']) . '-' . Str::random(4),
                'owner_name' => $data['owner_name'],
                'phone'      => $data['phone'] ?? null,
                'address'    => $data['address'] ?? null,
                'is_active'  => true,
            ]);

            // 2. Create Branch pertama
            $branch = Branch::create([
                'barbershop_id'         => $barbershop->id,
                'name'                  => $data['branch_name'],
                'code'                  => $code,
                'address'               => $data['address'] ?? null,
                'phone'                 => $data['phone'] ?? null,
                'open_time'             => '08:00:00',
                'close_time'            => '21:00:00',
                'is_active'             => true,
                'queue_timeout_minutes' => 60,
                'avg_service_minutes'   => 15,
            ]);

            // 3. Create Admin User — ikat ke barbershop (multi-branch) DAN cabang pertama (backward compat)
            $user = User::create([
                'name'          => $data['owner_name'],
                'email'         => $data['email'],
                'password'      => Hash::make($data['password']),
                'role'          => 'admin',
                'branch_id'     => $branch->id,
                'barbershop_id' => $barbershop->id,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat akun: ' . $e->getMessage());
        }

        // 4. Auto-login
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('admin.dashboard')
            ->with('success', "Selamat datang di BarberQ! Barbershop \"{$barbershop->name}\" dan cabang \"{$branch->name}\" (kode: {$branch->code}) telah berhasil didaftarkan.");
    }
}
