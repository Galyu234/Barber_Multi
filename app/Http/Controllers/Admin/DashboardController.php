<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barbershop;
use App\Models\Branch;
use App\Models\Queue;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // ── Super Admin ─────────────────────────────────────────────────────
        if ($user->isSuperAdmin()) {
            $totalBarbershops  = Barbershop::count();
            $totalBranches     = Branch::count();
            $totalActiveQueues = Queue::whereIn('status', ['waiting', 'in_progress', 'serving'])->count();
            $totalTodayQueues  = Queue::whereDate('joined_at', today())->count();

            $busiestBranch  = Branch::withCount(['queues' => fn($q) => $q->whereIn('status', ['waiting', 'in_progress', 'serving'])])->orderByDesc('queues_count')->first();
            $quietestBranch = Branch::withCount(['queues' => fn($q) => $q->whereIn('status', ['waiting', 'in_progress', 'serving'])])->orderBy('queues_count')->first();

            $branchStats = Branch::with('barbershop')
                ->withCount([
                    'queues as active_count' => fn($q) => $q->whereIn('status', ['waiting', 'in_progress', 'serving']),
                    'queues as today_count'  => fn($q) => $q->whereDate('joined_at', today()),
                ])
                ->orderByDesc('active_count')
                ->get();

            $dailyStats = Queue::select(
                DB::raw('DATE(joined_at) as date'),
                DB::raw('COUNT(*) as total')
            )->whereDate('joined_at', '>=', now()->subDays(7))
             ->groupBy('date')
             ->orderBy('date')
             ->get();

            return view('admin.dashboard', compact(
                'totalBarbershops', 'totalBranches', 'totalActiveQueues',
                'totalTodayQueues', 'busiestBranch', 'quietestBranch',
                'branchStats', 'dailyStats'
            ));
        }

        // ── Tenant Admin (multi-branch) ─────────────────────────────────────
        if ($user->isTenantAdmin()) {
            $branchIds = $user->managedBranchIds();

            $totalBranches     = count($branchIds);
            $totalActiveQueues = Queue::whereIn('branch_id', $branchIds)
                ->whereIn('status', ['waiting', 'in_progress', 'serving'])
                ->count();
            $totalTodayQueues  = Queue::whereIn('branch_id', $branchIds)
                ->whereDate('joined_at', today())
                ->count();

            $branchStats = Branch::with('barbershop')
                ->whereIn('id', $branchIds)
                ->withCount([
                    'queues as active_count' => fn($q) => $q->whereIn('status', ['waiting', 'in_progress', 'serving']),
                    'queues as today_count'  => fn($q) => $q->whereDate('joined_at', today()),
                ])
                ->orderByDesc('active_count')
                ->get();

            $dailyStats = Queue::select(
                DB::raw('DATE(joined_at) as date'),
                DB::raw('COUNT(*) as total')
            )->whereIn('branch_id', $branchIds)
             ->whereDate('joined_at', '>=', now()->subDays(7))
             ->groupBy('date')
             ->orderBy('date')
             ->get();

            $barbershop = $user->barbershop;

            return view('admin.dashboard', compact(
                'totalBranches', 'totalActiveQueues', 'totalTodayQueues',
                'branchStats', 'dailyStats', 'barbershop'
            ));
        }

        // ── Admin Cabang (single-branch legacy) ─────────────────────────────
        $branch = $user->branch;

        if (!$branch) {
            return view('admin.dashboard', [
                'branch'            => null,
                'totalActiveQueues' => 0,
                'totalTodayQueues'  => 0,
                'dailyStats'        => collect(),
            ]);
        }

        $totalActiveQueues = Queue::where('branch_id', $branch->id)
            ->whereIn('status', ['waiting', 'in_progress', 'serving'])
            ->count();

        $totalTodayQueues = Queue::where('branch_id', $branch->id)
            ->whereDate('joined_at', today())
            ->count();

        $dailyStats = Queue::select(
            DB::raw('DATE(joined_at) as date'),
            DB::raw('COUNT(*) as total')
        )->where('branch_id', $branch->id)
         ->whereDate('joined_at', '>=', now()->subDays(7))
         ->groupBy('date')
         ->orderBy('date')
         ->get();

        return view('admin.dashboard', compact(
            'branch', 'totalActiveQueues', 'totalTodayQueues', 'dailyStats'
        ));
    }
}
