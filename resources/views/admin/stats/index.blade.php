@extends('layouts.admin')

@section('title', 'Statistik')
@section('page-title', 'Statistik & Analitik')

@section('content')
<!-- Global Summary -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="stat-card text-center">
        <div class="text-3xl font-black text-blue-600">{{ $globalStats['total_active'] }}</div>
        <div class="text-xs text-slate-500 mt-1 font-medium">Antrian Aktif Sekarang</div>
    </div>
    <div class="stat-card text-center">
        <div class="text-3xl font-black text-amber-500">{{ $globalStats['total_today'] }}</div>
        <div class="text-xs text-slate-500 mt-1 font-medium">Total Hari Ini</div>
    </div>
    <div class="stat-card text-center">
        <div class="text-3xl font-black text-purple-600">{{ $globalStats['total_week'] }}</div>
        <div class="text-xs text-slate-500 mt-1 font-medium">Total 7 Hari</div>
    </div>
</div>

<!-- Chart -->
<div class="admin-card p-5 mb-6">
    <div class="flex items-center justify-between mb-5">
        <h3 class="font-semibold text-slate-800 text-sm">Tren Pelanggan — 7 Hari Terakhir</h3>
    </div>
    <div class="relative" style="min-height:180px">
        <canvas id="trendChart"></canvas>
    </div>
</div>

<!-- Per-Branch Stats -->
<div class="admin-card overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="font-semibold text-slate-800 text-sm">Statistik Per Cabang</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[560px]">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/50">
                    <th class="text-left py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider">Cabang</th>
                    <th class="text-center py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider">Aktif</th>
                    <th class="text-center py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider">Hari Ini</th>
                    <th class="text-center py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider hidden md:table-cell">7 Hari</th>
                    <th class="text-center py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider hidden lg:table-cell">Rata-rata Layanan</th>
                    <th class="text-center py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider hidden lg:table-cell">Peak Hour</th>
                    <th class="text-center py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider">Estimasi Tunggu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($branchStats as $b)
                @php
                    $status = $b['active_count'] <= 2 ? 'sepi' : ($b['active_count'] <= 6 ? 'sedang' : 'ramai');
                    $colors = ['sepi' => 'badge-green', 'sedang' => 'badge-yellow', 'ramai' => 'badge-red'];
                @endphp
                <tr class="table-row">
                    <td class="py-3.5 px-5">
                        <div class="font-semibold text-slate-800 text-sm">{{ $b['name'] }}</div>
                        <div class="text-xs text-slate-500">{{ $b['barbershop'] }} · {{ $b['code'] }}</div>
                    </td>
                    <td class="py-3.5 px-5 text-center">
                        <span class="badge {{ $colors[$status] }}">{{ $b['active_count'] }}</span>
                    </td>
                    <td class="py-3.5 px-5 text-center text-slate-700 font-semibold">{{ $b['today_count'] }}</td>
                    <td class="py-3.5 px-5 text-center text-slate-600 hidden md:table-cell">{{ $b['week_count'] }}</td>
                    <td class="py-3.5 px-5 text-center hidden lg:table-cell">
                        <span class="text-blue-600 font-semibold">{{ $b['avg_service'] }} mnt</span>
                    </td>
                    <td class="py-3.5 px-5 text-center hidden lg:table-cell">
                        <span class="text-amber-600 font-semibold">{{ $b['peak_hour'] }}</span>
                    </td>
                    <td class="py-3.5 px-5 text-center">
                        @if($b['active_count'] > 0)
                        <span class="text-amber-600 font-semibold">~{{ $b['estimated_wait'] }} mnt</span>
                        @else
                        <span class="text-green-600 font-semibold">Langsung</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartData = @json($chartData);
    const ctx = document.getElementById('trendChart');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(d => {
                const date = new Date(d.date);
                return date.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });
            }),
            datasets: [{
                label: 'Pelanggan',
                data: chartData.map(d => d.total),
                borderColor: 'rgba(245, 158, 11, 0.9)',
                backgroundColor: 'rgba(245, 158, 11, 0.08)',
                pointBackgroundColor: 'rgba(245, 158, 11, 1)',
                pointBorderColor: 'rgba(245, 158, 11, 0.5)',
                pointRadius: 5,
                pointHoverRadius: 7,
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#94a3b8',
                    bodyColor: '#f59e0b',
                    borderColor: 'rgba(245,158,11,0.3)',
                    borderWidth: 1,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: '#64748b', stepSize: 1 },
                    grid: { color: 'rgba(0,0,0,0.04)' }
                },
                x: {
                    ticks: { color: '#64748b' },
                    grid: { display: false }
                }
            }
        }
    });
</script>
@endpush
