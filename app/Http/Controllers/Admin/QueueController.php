<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Queue;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Queue::with(['branch.barbershop'])->latest('joined_at');

        // Tenant isolation
        if ($user->isSuperAdmin()) {
            // semua cabang
        } elseif ($user->isTenantAdmin()) {
            $query->whereIn('branch_id', $user->managedBranchIds());
        } else {
            $query->where('branch_id', $user->branch_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->boolean('today')) {
            $query->whereDate('joined_at', today());
        }

        $queues = $query->paginate(20)->withQueryString();

        // Daftar cabang untuk filter dropdown
        if ($user->isSuperAdmin()) {
            $branches = Branch::with('barbershop')->orderBy('name')->get();
        } elseif ($user->isTenantAdmin()) {
            $branches = Branch::where('barbershop_id', $user->barbershop_id)->orderBy('name')->get();
        } else {
            $branches = $user->branch ? collect([$user->branch]) : collect();
        }

        return view('admin.queues.index', compact('queues', 'branches'));
    }

    public function destroy(Queue $queue)
    {
        $user = auth()->user();
        if (!$user->canManageBranch($queue->branch_id)) {
            abort(403);
        }

        $queue->update(['status' => 'cancelled', 'finished_at' => now()]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "Antrian #{$queue->formatted_queue_number} berhasil dibatalkan.");
    }

    /**
     * AJAX: Real-time monitor — hanya tampilkan antrian aktif (waiting + in_progress + serving legacy).
     */
    public function apiMonitor(Request $request)
    {
        $user  = auth()->user();
        $query = Queue::with('branch.barbershop')
            ->whereIn('status', ['waiting', 'in_progress', 'serving'])
            ->orderBy('branch_id')
            ->orderBy('joined_at');

        if ($user->isSuperAdmin()) {
            // semua
        } elseif ($user->isTenantAdmin()) {
            $query->whereIn('branch_id', $user->managedBranchIds());
        } else {
            $query->where('branch_id', $user->branch_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $queues = $query->get()->map(fn($q) => [
            'id'           => $q->id,
            'queue_number' => $q->formatted_queue_number,
            'branch_name'  => $q->branch->name,
            'status'       => $q->status,
            'status_label' => $q->status_label,
            'joined_at'    => $q->joined_at->format('H:i'),
            'waiting_mins' => $q->joined_at->diffInMinutes(now()),
        ]);

        return response()->json([
            'queues'       => $queues,
            'total'        => $queues->count(),
            'last_updated' => now()->format('H:i:s'),
        ]);
    }
}
