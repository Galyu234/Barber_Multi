<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Queue;
use App\Services\QueueService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QueueController extends Controller
{
    public function __construct(
        private QueueService $queueService,
        private QrCodeService $qrCodeService
    ) {}

    // GET /queue/join/{branch_code}
    public function showJoin(Request $request, string $branchCode)
    {
        $branch = Branch::where('code', $branchCode)->where('is_active', true)->firstOrFail();

        $sessionToken = $request->session()->get("queue_token_{$branchCode}") ?? Str::random(40);
        $request->session()->put("queue_token_{$branchCode}", $sessionToken);

        // Cek apakah sudah antri
        $alreadyInQueue = Queue::where('customer_session', $sessionToken)
            ->where('branch_id', $branch->id)
            ->whereIn('status', ['waiting', 'in_progress', 'serving'])
            ->first();

        // Generate customer QR SVG jika sudah dalam antrian
        $customerQrSvg = null;
        if ($alreadyInQueue && $alreadyInQueue->queue_qr) {
            $statusUrl     = $this->qrCodeService->getCustomerStatusUrl($branchCode, $alreadyInQueue->queue_qr);
            $customerQrSvg = $this->qrCodeService->generateCustomerQrSvg($alreadyInQueue->queue_qr, 180);
        }

        return view('queue.join', compact('branch', 'alreadyInQueue', 'customerQrSvg'));
    }

    // POST /queue/join/{branch_code}
    public function join(Request $request, string $branchCode)
    {
        // ── SECURITY: Smart QR-centric lock ────────────────────────────────────────
        // Hanya blok jika:
        //  a) Customer punya lock ke cabang LAIN
        //  b) DAN masih ada antrian aktif di cabang yang terkunci itu
        // Jika antrian sebelumnya selesai → reset lock → izinkan join
        $scannedBranch = $request->session()->get('active_scanned_branch');

        if ($scannedBranch && $scannedBranch !== $branchCode) {
            return redirect()->route('branch.detail', $branchCode)
                ->with('error', 'Anda telah terkunci di cabang ' . $scannedBranch . '. Anda hanya dapat mengambil antrian di cabang yang pertama kali Anda scan.');
        }
        // ────────────────────────────────────────────────────────────────────────
        $sessionToken = $request->session()->get("queue_token_{$branchCode}") ?? Str::random(40);
        $request->session()->put("queue_token_{$branchCode}", $sessionToken);

        // ── SECURITY: Global 1-queue restriction ───────────────────────────────────
        $branch = Branch::where('code', $branchCode)->first();
        if ($branch) {
            $activeElsewhere = Queue::where('customer_session', $sessionToken)
                ->where('branch_id', '!=', $branch->id)
                ->whereIn('status', ['waiting', 'in_progress', 'serving'])
                ->with('branch')
                ->first();

            if ($activeElsewhere) {
                return redirect()->route('branch.detail', $branchCode)
                    ->with('error', "Anda masih dalam antrian di cabang {$activeElsewhere->branch->name} (No. {$activeElsewhere->formatted_queue_number}). Selesaikan atau batalkan antrian tersebut terlebih dahulu.");
            }
        }

        $result = $this->queueService->join($branchCode, $sessionToken);

        if (!$result['success']) {
            return redirect()->route('queue.join', $branchCode)
                ->with('error', $result['message']);
        }

        // Simpan queue_qr ke session agar bisa dipakai di halaman branch-detail
        if (!empty($result['queue_qr'])) {
            $request->session()->put("queue_qr_{$branchCode}", $result['queue_qr']);
        }

        $request->session()->put("queue_result_{$branchCode}", $result);

        // Redirect ke halaman status menggunakan queue_qr sebagai token
        $token = $result['queue_qr'] ?? $result['token'];
        return redirect()->route('queue.status', ['branch_code' => $branchCode, 'token' => $token]);
    }

    // GET /queue/leave/{branch_code}
    public function showLeave(Request $request, string $branchCode)
    {
        $branch = Branch::where('code', $branchCode)->where('is_active', true)->firstOrFail();

        $sessionToken = $request->session()->get("queue_token_{$branchCode}");
        $currentQueue = null;

        if ($sessionToken) {
            $currentQueue = Queue::where('customer_session', $sessionToken)
                ->where('branch_id', $branch->id)
                ->whereIn('status', ['waiting', 'in_progress', 'serving'])
                ->first();
        }

        return view('queue.leave', compact('branch', 'currentQueue'));
    }

    // POST /queue/leave/{branch_code}
    public function leave(Request $request, string $branchCode)
    {
        $sessionToken = $request->session()->get("queue_token_{$branchCode}");

        if (!$sessionToken) {
            return redirect()->route('queue.leave', $branchCode)
                ->with('error', 'Sesi tidak ditemukan. Anda belum pernah masuk antrian.');
        }

        $result = $this->queueService->leave($branchCode, $sessionToken);

        if (!$result['success']) {
            return redirect()->route('queue.leave', $branchCode)
                ->with('error', $result['message']);
        }

        // Hapus session token, queue_qr, dan lock cabang
        $request->session()->forget("queue_token_{$branchCode}");
        $request->session()->forget("queue_qr_{$branchCode}");
        $request->session()->forget('active_scanned_branch');

        return redirect()->route('queue.leave', $branchCode)
            ->with('success', $result['message'])
            ->with('queue_number', $result['queue_number']);
    }

    // GET /queue/status/{branch_code}/{token}
    public function status(Request $request, string $branchCode, string $token)
    {
        $branch = Branch::where('code', $branchCode)->firstOrFail();

        // Lookup by queue_qr dulu, fallback ke customer_session
        $queue = Queue::where('branch_id', $branch->id)
            ->where(function ($q) use ($token) {
                $q->where('queue_qr', $token)
                  ->orWhere('customer_session', $token);
            })
            ->latest('joined_at')
            ->firstOrFail();

        // Generate customer QR SVG (Hanya token agar QR tidak terlalu padat & mudah discan webcam laptop)
        $qrToken       = $queue->queue_qr ?? $token;
        $statusUrl     = $this->qrCodeService->getCustomerStatusUrl($branchCode, $qrToken);
        $customerQrSvg = $this->qrCodeService->generateCustomerQrSvg($qrToken, 180);

        return view('queue.status', compact('branch', 'queue', 'token', 'customerQrSvg', 'statusUrl'));
    }

    // AJAX GET /api/queue/status/{branch_code}/{token}
    public function apiStatus(string $branchCode, string $token)
    {
        $status = $this->queueService->getStatus($token, $branchCode);

        if (!$status) {
            return response()->json(['found' => false]);
        }

        return response()->json(array_merge($status, [
            'found'        => true,
            'last_updated' => now()->format('H:i:s'),
        ]));
    }
}
