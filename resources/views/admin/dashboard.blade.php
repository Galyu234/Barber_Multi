@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<!-- Global Stats Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">Antrian Aktif</div>
            <div class="w-8 h-8 rounded-lg bg-blue-500/15 flex items-center justify-center text-blue-400 text-sm">⚡</div>
        </div>
        <div class="text-3xl font-black text-slate-800">{{ $totalActiveQueues }}</div>
        <div class="text-xs text-gray-600 mt-1">Sedang mengantri</div>
    </div>

    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">Hari Ini</div>
            <div class="w-8 h-8 rounded-lg bg-amber-500/15 flex items-center justify-center text-amber-400 text-sm">📊</div>
        </div>
        <div class="text-3xl font-black text-slate-800">{{ $totalTodayQueues }}</div>
        <div class="text-xs text-gray-600 mt-1">Total pelanggan</div>
    </div>

    @if(auth()->user()->isSuperAdmin())
    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">Barbershop</div>
            <div class="w-8 h-8 rounded-lg bg-purple-500/15 flex items-center justify-center text-purple-400 text-sm">🏪</div>
        </div>
        <div class="text-3xl font-black text-slate-800">{{ $totalBarbershops }}</div>
        <div class="text-xs text-gray-600 mt-1">Terdaftar</div>
    </div>

    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">Cabang</div>
            <div class="w-8 h-8 rounded-lg bg-green-500/15 flex items-center justify-center text-green-400 text-sm">📍</div>
        </div>
        <div class="text-3xl font-black text-slate-800">{{ $totalBranches }}</div>
        <div class="text-xs text-gray-600 mt-1">Cabang aktif</div>
    </div>
    @elseif(auth()->user()->isTenantAdmin())
    {{-- Tenant Admin: Total Cabang Milik Barbershop --}}
    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">Cabang Saya</div>
            <div class="w-8 h-8 rounded-lg bg-purple-500/15 flex items-center justify-center text-purple-400 text-sm">📍</div>
        </div>
        <div class="text-3xl font-black text-slate-800">{{ $totalBranches ?? 0 }}</div>
        <div class="text-xs text-gray-600 mt-1">Total cabang</div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">Barbershop</div>
            <div class="w-8 h-8 rounded-lg bg-amber-500/15 flex items-center justify-center text-amber-400 text-sm">🏪</div>
        </div>
        <div class="text-3xl font-black text-slate-800 truncate text-lg">{{ auth()->user()->barbershop->name ?? '—' }}</div>
        <div class="text-xs text-gray-600 mt-1">Barbershop Anda</div>
    </div>
    @endif
</div>

{{-- Chart + Quick Info --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Daily Chart --}}
    <div class="admin-card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-semibold text-slate-800 text-sm">Pelanggan 7 Hari Terakhir</h3>
            <span class="text-xs text-slate-500">Antrian masuk per hari</span>
        </div>
        <div class="relative" style="min-height:160px">
            <canvas id="dailyChart"></canvas>
        </div>
    </div>

    {{-- Quick Info --}}
    <div class="flex flex-col gap-4">
        @if(auth()->user()->isSuperAdmin())
            @if(isset($busiestBranch) && $busiestBranch)
            <div class="admin-card p-5 flex-1 flex flex-col justify-center">
                <div class="text-xs text-slate-500 mb-2 uppercase tracking-wider font-bold">🔴 Paling Ramai</div>
                <div class="font-bold text-slate-900 text-sm">{{ $busiestBranch->name }}</div>
                <div class="text-3xl font-black text-red-500 mt-2">{{ $busiestBranch->queues_count }} <span class="text-sm font-semibold text-slate-500">antrian</span></div>
            </div>
            @endif

            @if(isset($quietestBranch) && $quietestBranch)
            <div class="admin-card p-5 flex-1 flex flex-col justify-center">
                <div class="text-xs text-slate-500 mb-2 uppercase tracking-wider font-bold">🟢 Paling Sepi</div>
                <div class="font-bold text-slate-900 text-sm">{{ $quietestBranch->name }}</div>
                <div class="text-3xl font-black text-green-500 mt-2">{{ $quietestBranch->queues_count }} <span class="text-sm font-semibold text-slate-500">antrian</span></div>
            </div>
            @endif
        @endif

        <div class="admin-card p-5 flex-1 flex flex-col justify-center">
            <div class="text-xs text-slate-500 mb-2 uppercase tracking-wider font-bold">⏱ Waktu Sekarang</div>
            <div class="text-2xl font-black text-blue-600" id="live-clock"></div>
            <div class="text-xs text-slate-500 mt-2 font-medium">{{ now()->isoFormat('dddd, D MMMM Y') }}</div>
        </div>
    </div>
</div>

@if(auth()->user()->isTenantAdmin())
{{-- Tenant Admin: Quick Action + Semua Cabang --}}
<div class="mb-6">
    <h3 class="text-sm font-bold text-slate-700 mb-3 uppercase tracking-wider">Aksi Cepat</h3>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('admin.branches.create') }}" class="admin-card p-4 flex flex-col items-center gap-2 text-center hover:border-blue-200 hover:bg-blue-50 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl group-hover:bg-blue-200 transition-colors">➕</div>
            <span class="text-xs font-semibold text-slate-700">Tambah Cabang</span>
        </a>
        <a href="{{ route('admin.branches.index') }}" class="admin-card p-4 flex flex-col items-center gap-2 text-center hover:border-purple-200 hover:bg-purple-50 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl group-hover:bg-purple-200 transition-colors">📍</div>
            <span class="text-xs font-semibold text-slate-700">Kelola Cabang</span>
        </a>
        <a href="{{ route('admin.queues.index') }}" class="admin-card p-4 flex flex-col items-center gap-2 text-center hover:border-amber-200 hover:bg-amber-50 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl group-hover:bg-amber-200 transition-colors">📋</div>
            <span class="text-xs font-semibold text-slate-700">Monitor Antrian</span>
        </a>
        <a href="{{ route('admin.scanner.index') }}" class="admin-card p-4 flex flex-col items-center gap-2 text-center hover:border-green-200 hover:bg-green-50 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-xl group-hover:bg-green-200 transition-colors">📲</div>
            <span class="text-xs font-semibold text-slate-700">Scan QR Pelanggan</span>
        </a>
    </div>
</div>

{{-- Tenant Admin: Status Semua Cabangnya --}}
@if(isset($branchStats) && $branchStats->count() > 0)
<div class="admin-card p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-slate-800 text-sm">Status Cabang Saya</h3>
        <a href="{{ route('admin.branches.index') }}" class="btn-primary text-xs">Kelola Cabang ⚙</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left min-w-[500px]">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/50">
                    <th class="py-3 px-4 text-xs text-slate-500 font-bold uppercase tracking-wider">Cabang</th>
                    <th class="text-center py-3 px-4 text-xs text-slate-500 font-bold uppercase tracking-wider">Aktif</th>
                    <th class="text-center py-3 px-4 text-xs text-slate-500 font-bold uppercase tracking-wider">Hari Ini</th>
                    <th class="text-center py-3 px-4 text-xs text-slate-500 font-bold uppercase tracking-wider">QR</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($branchStats as $b)
                @php
                    $active = $b->active_count ?? 0;
                    $status = $active <= 2 ? 'sepi' : ($active <= 6 ? 'sedang' : 'ramai');
                    $colors = ['sepi' => 'badge-green', 'sedang' => 'badge-yellow', 'ramai' => 'badge-red'];
                    $labels = ['sepi' => '🟢 Sepi', 'sedang' => '🟡 Sedang', 'ramai' => '🔴 Ramai'];
                @endphp
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3 px-4">
                        <div class="font-bold text-slate-900">{{ $b->name }}</div>
                        <div class="text-xs text-slate-500 font-mono">{{ $b->code }}</div>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="badge {{ $colors[$status] }}">{{ $labels[$status] }}</span>
                    </td>
                    <td class="py-3 px-4 text-center font-medium text-slate-600">{{ $b->today_count ?? 0 }}</td>
                    <td class="py-3 px-4 text-center">
                        <a href="{{ route('admin.branches.qrcode', $b->id) }}"
                           class="text-xs text-amber-600 hover:text-amber-700 font-bold">
                            📠 QR
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endif

@if(!auth()->user()->isSuperAdmin() && !auth()->user()->isTenantAdmin())
{{-- Admin Cabang (single-branch): Quick Action Cards --}}
@if(isset($branch) && $branch)
<div class="mb-6">
    <h3 class="text-sm font-bold text-slate-700 mb-3 uppercase tracking-wider">Aksi Cepat</h3>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('admin.profile.branch') }}" class="admin-card p-4 flex flex-col items-center gap-2 text-center hover:border-blue-200 hover:bg-blue-50 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl group-hover:bg-blue-200 transition-colors">🏪</div>
            <span class="text-xs font-semibold text-slate-700">Edit Profil Cabang</span>
        </a>
        <a href="{{ route('admin.queues.index') }}" class="admin-card p-4 flex flex-col items-center gap-2 text-center hover:border-amber-200 hover:bg-amber-50 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl group-hover:bg-amber-200 transition-colors">📋</div>
            <span class="text-xs font-semibold text-slate-700">Monitor Antrian</span>
        </a>
        <a href="{{ route('admin.scanner.index') }}" class="admin-card p-4 flex flex-col items-center gap-2 text-center hover:border-green-200 hover:bg-green-50 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-xl group-hover:bg-green-200 transition-colors">📲</div>
            <span class="text-xs font-semibold text-slate-700">Scan QR Pelanggan</span>
        </a>
        <a href="{{ route('admin.branches.qrcode', $branch->id) }}" class="admin-card p-4 flex flex-col items-center gap-2 text-center hover:border-purple-200 hover:bg-purple-50 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl group-hover:bg-purple-200 transition-colors">🔲</div>
            <span class="text-xs font-semibold text-slate-700">QR Cabang Saya</span>
        </a>
    </div>
</div>
@endif
@endif


<!-- Branch Stats Table (Super Admin Only) -->
@if(auth()->user()->isSuperAdmin())
<div class="admin-card p-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-slate-800 text-sm">Status Semua Cabang</h3>
        <div class="flex gap-2">
            <a href="{{ route('admin.branches.index') }}" class="btn-primary text-xs flex items-center justify-center">Kelola Cabang ⚙</a>
            <a href="{{ route('admin.queues.index') }}" class="btn-secondary text-xs flex items-center justify-center">Lihat Monitor →</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left min-w-[640px]">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/50">
                    <th class="py-3 px-4 text-xs text-slate-500 font-bold uppercase tracking-wider rounded-tl-lg">Cabang</th>
                    <th class="text-center py-3 px-4 text-xs text-slate-500 font-bold uppercase tracking-wider">Aktif</th>
                    <th class="text-center py-3 px-4 text-xs text-slate-500 font-bold uppercase tracking-wider">Hari Ini</th>
                    <th class="text-center py-3 px-4 text-xs text-slate-500 font-bold uppercase tracking-wider">Status</th>
                    <th class="text-center py-3 px-4 text-xs text-slate-500 font-bold uppercase tracking-wider rounded-tr-lg">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($branchStats as $b)
                @php
                    $active = $b->active_count ?? 0;
                    $status = $active <= 2 ? 'sepi' : ($active <= 6 ? 'sedang' : 'ramai');
                    $colors = ['sepi' => 'badge-green', 'sedang' => 'badge-yellow', 'ramai' => 'badge-red'];
                    $labels = ['sepi' => '🟢 Sepi', 'sedang' => '🟡 Sedang', 'ramai' => '🔴 Ramai'];
                @endphp
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3 px-4">
                        <div class="font-bold text-slate-900">{{ $b->name }}</div>
                        <div class="text-xs text-slate-500 font-mono">{{ $b->code ?? '' }}</div>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="text-lg font-black text-slate-800">{{ $active }}</span>
                    </td>
                    <td class="py-3 px-4 text-center font-medium text-slate-600">{{ $b->today_count ?? 0 }}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="badge {{ $colors[$status] }}">{{ $labels[$status] }}</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ route('admin.branches.qrcode', $b->id) }}"
                               class="text-xs text-amber-600 hover:text-amber-700 font-bold transition-colors flex items-center gap-1">
                                📠 QR Code
                            </a>
                            <span class="text-slate-300">|</span>
                            <a href="{{ route('branch.detail', $b->code) }}" target="_blank"
                               class="text-xs text-blue-600 hover:text-blue-700 font-bold transition-colors flex items-center gap-1">
                                Publik →
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Live clock
    function updateClock() {
        const now = new Date();
        document.getElementById('live-clock').textContent =
            now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Daily chart
    const ctx = document.getElementById('dailyChart');
    const chartData = @json($dailyStats);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.map(d => {
                const date = new Date(d.date);
                return date.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });
            }),
            datasets: [{
                label: 'Pelanggan',
                data: chartData.map(d => d.total),
                backgroundColor: 'rgba(59, 130, 246, 0.4)',
                borderColor: 'rgba(59, 130, 246, 0.8)',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#9ca3af',
                    bodyColor: '#e5e7eb',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: '#6b7280', stepSize: 1 },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                },
                x: {
                    ticks: { color: '#6b7280' },
                    grid: { display: false }
                }
            }
        }
    });
</script>
@endpush
