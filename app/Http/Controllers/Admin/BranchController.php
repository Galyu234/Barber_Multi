<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barbershop;
use App\Models\Branch;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    public function __construct(private QrCodeService $qrCodeService) {}

    /**
     * Daftar cabang:
     * - Super admin: semua cabang
     * - Tenant admin (barbershop_id): semua cabang miliknya
     * - Admin cabang lama (branch_id): hanya cabangnya sendiri
     */
    public function index()
    {
        $user  = auth()->user();
        $query = Branch::with('barbershop')
            ->withCount(['queues' => fn($q) => $q->whereIn('status', ['waiting', 'in_progress', 'serving'])]);

        if ($user->isSuperAdmin()) {
            // tampilkan semua
        } elseif ($user->isTenantAdmin()) {
            $query->where('barbershop_id', $user->barbershop_id);
        } else {
            $query->where('id', $user->branch_id);
        }

        $branches = $query->latest()->paginate(20);
        return view('admin.branches.index', compact('branches'));
    }

    /**
     * Form tambah cabang baru — untuk tenant admin & super admin.
     */
    public function create()
    {
        $user = auth()->user();

        // Hanya Tenant Admin (pemilik barbershop) yang boleh menambah cabang
        if ($user->isTenantAdmin()) {
            $barbershop = $user->barbershop;
            return view('admin.branches.create', compact('barbershop'));
        }

        abort(403, 'Hanya admin barbershop yang memiliki akses untuk menambah cabang baru.');
    }

    /**
     * Simpan cabang baru, generate branch_code & QR otomatis.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->isTenantAdmin()) {
            abort(403, 'Hanya admin barbershop yang memiliki akses untuk menambah cabang baru.');
        }

        $data = $request->validate([
            'name'                  => 'required|string|max:100',
            'address'               => 'nullable|string|max:300',
            'phone'                 => 'nullable|string|max:20',
            'open_time'             => 'required',
            'close_time'            => 'required',
            'queue_timeout_minutes' => 'required|integer|min:10|max:480',
            'avg_service_minutes'   => 'required|integer|min:5|max:120',
        ]);

        $barbershopId = $user->barbershop_id;

        // Generate branch_code unik
        $barbershop = Barbershop::findOrFail($barbershopId);
        $prefix     = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $barbershop->name), 0, 2));
        if (strlen($prefix) < 2) $prefix = str_pad($prefix, 2, 'X');

        $suffix = 1;
        do {
            $code = $prefix . str_pad($suffix, 3, '0', STR_PAD_LEFT);
            $suffix++;
        } while (Branch::where('code', $code)->exists());

        $branch = Branch::create([
            'barbershop_id'         => $barbershopId,
            'name'                  => $data['name'],
            'code'                  => $code,
            'address'               => $data['address'] ?? null,
            'phone'                 => $data['phone'] ?? null,
            'open_time'             => $data['open_time'],
            'close_time'            => $data['close_time'],
            'is_active'             => true,
            'queue_timeout_minutes' => $data['queue_timeout_minutes'],
            'avg_service_minutes'   => $data['avg_service_minutes'],
        ]);

        return redirect()->route('admin.branches.index')
            ->with('success', "Cabang \"{$branch->name}\" berhasil dibuat! Kode cabang: {$branch->code}");
    }

    /**
     * Edit cabang — super admin atau tenant admin yang memiliki cabang ini.
     */
    public function edit(Branch $branch)
    {
        $this->authorizeBranch($branch);
        $barbershops = Barbershop::where('is_active', true)->get();
        return view('admin.branches.edit', compact('branch', 'barbershops'));
    }

    public function update(Request $request, Branch $branch)
    {
        $this->authorizeBranch($branch);

        $data = $request->validate([
            'name'                  => 'required|string|max:100',
            'address'               => 'nullable|string',
            'phone'                 => 'nullable|string|max:20',
            'open_time'             => 'required',
            'close_time'            => 'required',
            'queue_timeout_minutes' => 'required|integer|min:10|max:480',
            'avg_service_minutes'   => 'required|integer|min:5|max:120',
            'is_active'             => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $branch->update($data);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Cabang berhasil diperbarui!');
    }

    public function destroy(Branch $branch)
    {
        $this->authorizeBranch($branch);

        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isTenantAdmin()) {
            abort(403, 'Hanya Super Admin atau pemilik barbershop yang dapat menghapus cabang.');
        }

        $branch->delete();
        return redirect()->route('admin.branches.index')
            ->with('success', 'Cabang berhasil dihapus.');
    }

    public function qrCode(Branch $branch)
    {
        $this->authorizeBranch($branch);
        $branchUrl  = $this->qrCodeService->getBranchUrl($branch->code);
        $branchSvg  = $this->qrCodeService->generateSvg($branchUrl, 250);
        $validation = $this->qrCodeService->validateAppUrl();

        return view('admin.branches.qrcode', compact('branch', 'branchUrl', 'branchSvg', 'validation'));
    }

    public function downloadQr(Branch $branch, string $type = 'branch')
    {
        $this->authorizeBranch($branch);
        if ($type !== 'branch') abort(404);

        $url      = $this->qrCodeService->getBranchUrl($branch->code);
        $filename = "QR_{$branch->code}_branch.png";

        try {
            $pngData = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(600)
                ->margin(2)
                ->generate($url);

            return response($pngData, 200, [
                'Content-Type'        => 'image/png',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (\Throwable $e) {
            return $this->downloadQrSvg($branch);
        }
    }

    public function downloadQrSvg(Branch $branch)
    {
        $this->authorizeBranch($branch);

        $url      = $this->qrCodeService->getBranchUrl($branch->code);
        $svg      = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(600)->margin(2)->generate($url);
        $filename = "QR_{$branch->code}.svg";

        return response($svg, 200, [
            'Content-Type'        => 'image/svg+xml',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ── Private Helpers ─────────────────────────────────────────────────────

    private function authorizeBranch(Branch $branch): void
    {
        if (!auth()->user()->canManageBranch($branch->id)) {
            abort(403);
        }
    }
}
