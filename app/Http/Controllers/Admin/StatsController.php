<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Queue;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $branchQuery = Branch::query();
        $queueQuery  = Queue::query();

        if (!$user->isSuperAdmin()) {
            $branchQuery->where('id', $user->branch_id);
            $queueQuery->where('branch_id', $user->branch_id);
        }

        // Statistik per cabang
        $branchStats = $branchQuery->with('barbershop')
            ->withCount([
                'queues as active_count'   => fn($q) => $q->whereIn('status', ['waiting', 'serving']),
                'queues as today_count'    => fn($q) => $q->whereDate('joined_at', today()),
                'queues as week_count'     => fn($q) => $q->whereDate('joined_at', '>=', now()->subDays(7)),
            ])
            ->get()
            ->map(function ($b) {
                $avgService = Queue::where('branch_id', $b->id)
                    ->whereNotNull('finished_at')
                    ->whereDate('joined_at', today())
                    ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, joined_at, finished_at)) as avg_minutes')
                    ->value('avg_minutes');

                $peakHour = Queue::where('branch_id', $b->id)
                    ->whereDate('joined_at', today())
                    ->selectRaw('HOUR(joined_at) as hour, COUNT(*) as cnt')
                    ->groupBy('hour')
                    ->orderByDesc('cnt')
                    ->first();

                return [
                    'id'           => $b->id,
                    'name'         => $b->name,
                    'code'         => $b->code,
                    'barbershop'   => $b->barbershop->name ?? '-',
                    'active_count' => $b->active_count,
                    'today_count'  => $b->today_count,
                    'week_count'   => $b->week_count,
                    'avg_service'  => round($avgService ?? $b->avg_service_minutes),
                    'estimated_wait' => $b->active_count * $b->avg_service_minutes,
                    'peak_hour'    => $peakHour ? sprintf('%02d:00', $peakHour->hour) : '-',
                ];
            });

        // Grafik 7 hari terakhir
        $dailyChart = $queueQuery->clone()
            ->select(DB::raw('DATE(joined_at) as date'), DB::raw('COUNT(*) as total'))
            ->whereDate('joined_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Isi hari yang kosong
        $chartData = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date  = now()->subDays($i)->format('Y-m-d');
            $found = $dailyChart->firstWhere('date', $date);
            $chartData->push(['date' => $date, 'total' => $found ? $found->total : 0]);
        }

        $globalStats = [
            'total_active'  => $queueQuery->clone()->whereIn('status', ['waiting', 'serving'])->count(),
            'total_today'   => $queueQuery->clone()->whereDate('joined_at', today())->count(),
            'total_week'    => $queueQuery->clone()->whereDate('joined_at', '>=', now()->subDays(7))->count(),
        ];

        return view('admin.stats.index', compact('branchStats', 'chartData', 'globalStats'));
    }
}
