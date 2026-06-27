<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barbershop;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BarbershopController extends Controller
{
    public function index()
    {
        $barbershops = Barbershop::withCount('branches')->latest()->paginate(10);
        return view('admin.barbershops.index', compact('barbershops'));
    }

    public function create()
    {
        return view('admin.barbershops.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'owner_name' => 'required|string|max:100',
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'is_active'  => 'boolean',
        ]);

        $data['slug']      = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        Barbershop::create($data);

        return redirect()->route('admin.barbershops.index')
            ->with('success', 'Barbershop berhasil ditambahkan!');
    }

    public function show(Barbershop $barbershop)
    {
        $this->authorizeBarbershop($barbershop);
        
        $barbershop->load([
            'branches' => function ($q) {
                $q->withCount(['queues as active_queues_count' => function ($q) {
                    $q->whereIn('status', ['waiting', 'serving']);
                }]);
            },
            'users'
        ]);

        return view('admin.barbershops.show', compact('barbershop'));
    }

    public function edit(Barbershop $barbershop)
    {
        $this->authorizeBarbershop($barbershop);
        return view('admin.barbershops.edit', compact('barbershop'));
    }

    public function update(Request $request, Barbershop $barbershop)
    {
        $this->authorizeBarbershop($barbershop);

        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'owner_name' => 'required|string|max:100',
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'is_active'  => 'boolean',
        ]);

        $data['slug']      = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $barbershop->update($data);

        return redirect()->route('admin.barbershops.index')
            ->with('success', 'Barbershop berhasil diperbarui!');
    }

    public function destroy(Barbershop $barbershop)
    {
        $this->authorizeBarbershop($barbershop);
        $barbershop->delete();
        return redirect()->route('admin.barbershops.index')
            ->with('success', 'Barbershop berhasil dihapus.');
    }

    /**
     * Toggle suspend/aktifkan tenant barbershop.
     * Saat suspend, semua branch dinonaktifkan.
     * Saat aktifkan, semua branch diaktifkan kembali.
     */
    public function toggleSuspend(Barbershop $barbershop)
    {
        $newStatus = !$barbershop->is_active;

        $barbershop->update(['is_active' => $newStatus]);

        // Sync semua branch dengan status barbershop
        $barbershop->branches()->update(['is_active' => $newStatus]);

        $action = $newStatus ? 'diaktifkan' : 'disuspend';
        return redirect()->route('admin.barbershops.index')
            ->with('success', "Barbershop \"{$barbershop->name}\" berhasil {$action}.");
    }

    private function authorizeBarbershop(Barbershop $barbershop): void
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }
    }
}
