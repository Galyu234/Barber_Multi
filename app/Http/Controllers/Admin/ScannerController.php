<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Queue;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    /**
     * Halaman scanner QR untuk admin.
     * Super admin dapat pilih cabang; admin tenant melihat semua cabang miliknya.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            $branches = Branch::with('barbershop')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        } elseif ($user->isTenantAdmin()) {
            $branches = Branch::where('barbershop_id', $user->barbershop_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        } else {
            $branches = $user->branch ? collect([$user->branch]) : collect();
        }

        $selectedBranchId = $request->get('branch_id', optional($branches->first())->id);

        return view('admin.scanner.index', compact('branches', 'selectedBranchId'));
    }

    /**
     * AJAX: Lookup antrian berdasarkan queue_qr token yang discan.
     * Kembalikan status saat ini agar frontend bisa menampilkan tombol yang tepat.
     */
    public function lookup(Request $request)
    {
        $token = $request->input('token');
        \Illuminate\Support\Facades\Log::info("Scanner lookup attempt with token: " . $token);

        if (!$token) {
            return response()->json(['found' => false, 'message' => 'Token tidak ditemukan.']);
        }

        $user = auth()->user();
        $managedBranchIds = $user->managedBranchIds();

        $queueQuery = Queue::with('branch.barbershop')
            ->where(function($q) use ($token) {
                $q->where('queue_qr', $token)
                  ->orWhere('customer_session', $token);
                
                // Jika token berupa angka (manual input nomor antrian)
                if (is_numeric($token)) {
                    $q->orWhere('queue_number', (int) $token);
                }
            });

        // Filter berdasarkan cabang yang bisa dikelola agar tidak bentrok jika nomor antrian sama antar cabang
        if (!empty($managedBranchIds)) {
            $queueQuery->whereIn('branch_id', $managedBranchIds);
        }

        $queue = $queueQuery->latest('joined_at')->first();

        if (!$queue) {
            \Illuminate\Support\Facades\Log::warning("Scanner lookup failed: Queue not found for token: " . $token);
            return response()->json(['found' => false, 'message' => 'Antrian tidak ditemukan untuk QR / Nomor ini.']);
        }

        // Double check otorisasi tenant (meski sudah difilter di atas, aman untuk dipertahankan)
        $user = auth()->user();
        if (!$user->canManageBranch($queue->branch_id)) {
            return response()->json(['found' => false, 'message' => 'Akses ditolak. QR bukan milik cabang Anda.']);
        }

        // Tentukan aksi yang tersedia berdasarkan status
        $action = $this->resolveAction($queue->status);

        return response()->json([
            'found'        => true,
            'queue_id'     => $queue->id,
            'queue_number' => $queue->formatted_queue_number,
            'status'       => $queue->status,
            'status_label' => $queue->status_label,
            'branch_name'  => $queue->branch->name,
            'joined_at'    => $queue->joined_at->format('H:i'),
            'position'     => $queue->position,
            'action'       => $action,           // 'start' | 'finish' | 'done' | null
            'can_complete' => $action !== null && $action !== 'done',
        ]);
    }

    /**
     * AJAX: Majukan status antrian — implementasi two-step lifecycle:
     *   waiting     → in_progress  (Mulai Cukur)
     *   in_progress → completed    (Selesaikan Antrian)
     *   completed   → (tampilkan pesan selesai, tidak ada perubahan)
     *
     * Tetap backward-compatible dengan status lama serving/done.
     */
    public function complete(Request $request)
    {
        $request->validate([
            'queue_id' => 'required|exists:queues,id',
        ]);

        $queue = Queue::with('branch')->findOrFail($request->queue_id);

        // Otorisasi tenant
        $user = auth()->user();
        if (!$user->canManageBranch($queue->branch_id)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        switch ($queue->status) {

            // ── Step 1: Mulai Cukur ──────────────────────────────────────────
            case 'waiting':
                $queue->update([
                    'status'    => 'in_progress',
                    'served_at' => now(),
                ]);
                return response()->json([
                    'success'    => true,
                    'new_status' => 'in_progress',
                    'message'    => "Antrian #{$queue->formatted_queue_number} — Sedang Dicukur! ✂️",
                    'action'     => 'finish',
                ]);

            // ── Step 2: Selesaikan ───────────────────────────────────────────
            case 'in_progress':
            case 'serving':     // backward compat untuk antrian lama
                $queue->update([
                    'status'      => 'completed',
                    'finished_at' => now(),
                ]);
                return response()->json([
                    'success'    => true,
                    'new_status' => 'completed',
                    'message'    => "Antrian #{$queue->formatted_queue_number} — Selesai! ✅",
                    'action'     => 'done',
                ]);

            // ── Sudah Selesai ────────────────────────────────────────────────
            case 'completed':
            case 'done':
                return response()->json([
                    'success'    => false,
                    'new_status' => $queue->status,
                    'message'    => "Antrian #{$queue->formatted_queue_number} sudah selesai.",
                    'action'     => 'done',
                ], 400);

            default:
                return response()->json([
                    'success' => false,
                    'message' => "Antrian #{$queue->formatted_queue_number} berstatus {$queue->status_label}, tidak dapat diubah.",
                ], 400);
        }
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Tentukan aksi scanner berikutnya berdasarkan status antrian.
     *
     * @return string|null  'start' | 'finish' | 'done' | null
     */
    private function resolveAction(string $status): ?string
    {
        return match ($status) {
            'waiting'     => 'start',
            'in_progress',
            'serving'     => 'finish',
            'completed',
            'done'        => 'done',
            default       => null,
        };
    }
}
