<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class HomeController extends Controller
{
    public function index()
    {
        $branches = Branch::with('barbershop')
            ->where('is_active', true)
            ->withCount(['queues as active_queue_count' => fn($q) => $q->whereIn('status', ['waiting', 'serving', 'in_progress'])])
            ->orderBy('name')
            ->get();

        return view('public.home', compact('branches'));
    }

    // AJAX: Semua cabang aktif + jumlah antrian (untuk polling di beranda)
    public function apiBranches()
    {
        $branches = Branch::with('barbershop')
            ->where('is_active', true)
            ->withCount(['queues as active_queue_count' => fn($q) => $q->whereIn('status', ['waiting', 'serving', 'in_progress'])])
            ->orderBy('name')
            ->get()
            ->map(fn($b) => [
                'id'             => $b->id,
                'name'           => $b->name,
                'code'           => $b->code,
                'barbershop'     => $b->barbershop->name ?? '',
                'address'        => $b->address,
                'phone'          => $b->phone,
                'is_open'        => $b->isOpen(),
                'open_time'      => substr($b->open_time, 0, 5),
                'close_time'     => substr($b->close_time, 0, 5),
                'queue_count'    => $b->active_queue_count,
                'queue_status'   => $b->queue_status,
                'queue_label'    => $b->queue_status_label,
                'estimated_wait' => $b->estimated_wait_minutes,
            ]);

        return response()->json([
            'branches'     => $branches,
            'last_updated' => now()->format('H:i:s'),
        ]);
    }

    // Halaman monitor antrian publik (view-only, tanpa join)
    public function queueMonitor(string $code)
    {
        $branch = Branch::with('barbershop')
            ->where('code', $code)
            ->where('is_active', true)
            ->firstOrFail();

        $queues = $branch->queues()
            ->whereIn('status', ['waiting', 'serving', 'in_progress'])
            ->orderBy('joined_at')
            ->get();

        return view('public.queue-monitor', compact('branch', 'queues'));
    }

    public function branchDetail(Request $request, string $code)
    {
        $branch = Branch::with('barbershop')
            ->where('code', $code)
            ->where('is_active', true)
            ->firstOrFail();

        // ── Smart Session Lock ──────────────────────────────────────────────────
        $lockedBranch = $request->session()->get('active_scanned_branch');

        // If arriving via direct QR scan (not from list), evaluate the lock:
        if ($request->query('from') !== 'list') {
            if (!$lockedBranch) {
                // No existing lock — set it to this branch
                $request->session()->put('active_scanned_branch', $code);
                $lockedBranch = $code;
            } elseif ($lockedBranch !== $code) {
                // Has a lock to a DIFFERENT branch — check if that queue is still active
                $lockedBranchModel = Branch::where('code', $lockedBranch)->first();
                $sessionToken = $request->session()->get("queue_token_{$lockedBranch}");
                $hasActiveQueue = false;

                if ($lockedBranchModel && $sessionToken) {
                    $hasActiveQueue = Queue::where('branch_id', $lockedBranchModel->id)
                        ->where('customer_session', $sessionToken)
                        ->whereIn('status', ['waiting', 'serving'])
                        ->exists();
                }

                // If no active queue at old branch → release lock, set to new branch
                if (!$hasActiveQueue) {
                    $request->session()->put('active_scanned_branch', $code);
                    $lockedBranch = $code;
                }
                // else: keep old lock — customer still active at the locked branch
            }
        }
        // ────────────────────────────────────────────────────────────────────────

        $queues = $branch->queues()
            ->whereIn('status', ['waiting', 'serving'])
            ->orderBy('joined_at')
            ->get();

        $sessionToken = $request->session()->get("queue_token_{$code}");
        $queueQr      = $request->session()->get("queue_qr_{$code}");
        $myQueue      = null;

        if ($queueQr || $sessionToken) {
            $myQueue = \App\Models\Queue::where('branch_id', $branch->id)
                ->where(function ($q) use ($queueQr, $sessionToken) {
                    if ($queueQr)      $q->where('queue_qr', $queueQr);
                    if ($sessionToken) $q->orWhere('customer_session', $sessionToken);
                })
                ->whereIn('status', ['waiting', 'serving'])
                ->first();
        }

        // Cabang lain untuk section "Cabang Lainnya"
        $otherBranches = Branch::with('barbershop')
            ->where('is_active', true)
            ->where('id', '!=', $branch->id)
            ->withCount(['queues as active_queue_count' => fn($q) => $q->whereIn('status', ['waiting', 'serving'])])
            ->orderBy('name')
            ->get();

        return view('public.branch-detail', compact('branch', 'queues', 'myQueue', 'otherBranches'));
    }

    // AJAX endpoint untuk branch detail
    public function apiBranchQueue(string $code)
    {
        $branch = Branch::where('code', $code)->firstOrFail();

        $queues = $branch->queues()
            ->whereIn('status', ['waiting', 'serving'])
            ->orderBy('joined_at')
            ->get()
            ->map(fn($q) => [
                'id'           => $q->id,
                'queue_number' => $q->formatted_queue_number,
                'status'       => $q->status,
                'status_label' => $q->status_label,
                'joined_at'    => $q->joined_at->format('H:i'),
            ]);

        return response()->json([
            'branch'       => [
                'name'           => $branch->name,
                'queue_count'    => $queues->count(),
                'queue_status'   => $branch->queue_status,
                'estimated_wait' => $branch->estimated_wait_minutes,
                'is_open'        => $branch->isOpen(),
            ],
            'queues'       => $queues,
            'last_updated' => now()->format('H:i:s'),
        ]);
    }
}
