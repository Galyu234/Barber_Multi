<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BranchProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        if ($user->isSuperAdmin() || !$user->branch_id) {
            abort(403, 'Hanya admin cabang yang dapat mengakses profil ini.');
        }

        $branch = $user->branch()->with('barbershop')->firstOrFail();
        return view('admin.profile.branch', compact('branch'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        if ($user->isSuperAdmin() || !$user->branch_id) {
            abort(403);
        }

        $branch = $user->branch()->with('barbershop')->firstOrFail();
        $barbershop = $branch->barbershop;

        $data = $request->validate([
            'barbershop_name' => 'required|string|max:100',
            'owner_name'      => 'required|string|max:100',
            'barbershop_phone'=> 'nullable|string|max:20',
            'barbershop_address'=> 'nullable|string',
            'logo'            => 'nullable|image|max:2048',
            
            'branch_name'     => 'required|string|max:100',
            'branch_phone'    => 'nullable|string|max:20',
            'branch_address'  => 'nullable|string',
            'open_time'       => 'required',
            'close_time'      => 'required',
            'avg_service_minutes' => 'required|integer|min:5|max:120',
        ]);

        // Update Barbershop
        $barbershopData = [
            'name'       => $data['barbershop_name'],
            'owner_name' => $data['owner_name'],
            'phone'      => $data['barbershop_phone'],
            'address'    => $data['barbershop_address'],
        ];

        if ($request->hasFile('logo')) {
            $barbershopData['logo'] = $request->file('logo')->store('logos', 'public');
        }
        $barbershop->update($barbershopData);

        // Update Branch
        $branch->update([
            'name'       => $data['branch_name'],
            'phone'      => $data['branch_phone'],
            'address'    => $data['branch_address'],
            'open_time'  => $data['open_time'],
            'close_time' => $data['close_time'],
            'avg_service_minutes' => $data['avg_service_minutes'],
        ]);

        return redirect()->route('admin.profile.branch')->with('success', 'Profil cabang berhasil diperbarui!');
    }
}
