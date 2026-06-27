@extends('layouts.admin')

@section('title', 'Monitor Antrian')
@section('page-title', 'Monitor Antrian Real-time')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-700 text-sm font-medium transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Dashboard
    </a>
</div>

<!-- Filter Bar -->
<div class="admin-card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[140px]">
            <label class="form-label">Cabang</label>
            <select name="branch_id" class="form-input">
                <option value="">Semua Cabang</option>
                @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                    {{ $b->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[140px]">
            <label class="form-label">Status</label>
            <select name="status" class="form-input">
                <option value="">Semua Status</option>
                <option value="waiting"     {{ request('status') == 'waiting'     ? 'selected' : '' }}>Menunggu</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Sedang Dicukur</option>
                <option value="completed"   {{ request('status') == 'completed'   ? 'selected' : '' }}>Selesai (Baru)</option>
                <option value="serving"     {{ request('status') == 'serving'     ? 'selected' : '' }}>Dilayani (Legacy)</option>
                <option value="done"        {{ request('status') == 'done'        ? 'selected' : '' }}>Selesai (Legacy)</option>
                <option value="timeout"     {{ request('status') == 'timeout'     ? 'selected' : '' }}>Timeout</option>
                <option value="cancelled"   {{ request('status') == 'cancelled'   ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="today" value="1" {{ request('today') ? 'checked' : '' }} class="rounded border-slate-300">
                <span class="text-sm text-slate-600 font-medium">Hari ini saja</span>
            </label>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary py-2">Filter</button>
            <a href="{{ route('admin.queues.index') }}" class="btn-secondary py-2">Reset</a>
        </div>
    </form>
</div>

<!-- Live Stats Bar -->
<div id="live-stats-bar" class="grid grid-cols-3 gap-4 mb-5">
    <div class="stat-card text-center">
        <div id="live-waiting" class="text-2xl font-black text-yellow-500">–</div>
        <div class="text-xs text-slate-500 mt-0.5 font-medium">Menunggu</div>
    </div>
    <div class="stat-card text-center">
        <div id="live-serving" class="text-2xl font-black text-blue-500">–</div>
        <div class="text-xs text-slate-500 mt-0.5 font-medium">Sedang Dicukur</div>
    </div>
    <div class="stat-card text-center flex flex-col items-center justify-center">
        <div class="flex items-center gap-1.5">
            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            <span id="live-time" class="text-xs text-green-600 font-semibold">Live</span>
        </div>
        <div class="text-xs text-slate-500 mt-1 font-medium">Update otomatis</div>
    </div>
</div>

<!-- Queue Table -->
<div class="admin-card overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <h3 class="font-semibold text-slate-800 text-sm">Daftar Antrian</h3>
        <span class="text-xs text-slate-500">{{ $queues->total() }} total antrian</span>
    </div>

    <div id="queue-table-body" class="overflow-x-auto">
        <table class="w-full text-sm min-w-[560px]">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/50">
                    <th class="text-left py-3 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider">#</th>
                    <th class="text-left py-3 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider">Cabang</th>
                    <th class="text-center py-3 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider">Status</th>
                    <th class="text-center py-3 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider hidden md:table-cell">Masuk</th>
                    <th class="text-center py-3 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider hidden lg:table-cell">Tunggu</th>
                    <th class="text-right py-3 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($queues as $q)
                <tr class="table-row" id="queue-row-{{ $q->id }}">
                    <td class="py-3 px-5">
                        <span class="text-lg font-black text-slate-800">{{ str_pad($q->queue_number, 3, '0', STR_PAD_LEFT) }}</span>
                    </td>
                    <td class="py-3 px-5">
                        <div class="text-sm font-semibold text-slate-800">{{ $q->branch->name }}</div>
                        <div class="text-xs text-slate-500">{{ $q->branch->code }}</div>
                    </td>
                    <td class="py-3 px-5 text-center">
                        <span class="badge badge-{{ $q->status_color }}">{{ $q->status_label }}</span>
                    </td>
                    <td class="py-3 px-5 text-center text-slate-500 text-xs hidden md:table-cell">
                        {{ $q->joined_at->format('H:i') }}
                    </td>
                    <td class="py-3 px-5 text-center hidden lg:table-cell">
                        @if(in_array($q->status, ['waiting', 'serving']))
                        <span class="text-xs font-semibold {{ $q->joined_at->diffInMinutes(now()) > 30 ? 'text-red-500' : 'text-slate-500' }}">
                            {{ $q->joined_at->diffInMinutes(now()) }} mnt
                        </span>
                        @else
                        <span class="text-xs text-slate-400">–</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-right">
                        @if(in_array($q->status, ['waiting', 'serving']))
                        <form action="{{ route('admin.queues.destroy', $q) }}" method="POST"
                              onsubmit="return confirm('Batalkan antrian #{{ $q->queue_number }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger">Batalkan</button>
                        </form>
                        @else
                        <span class="text-xs text-slate-400">–</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-16 text-center text-slate-500">
                        <div class="text-3xl mb-3">✅</div>
                        <p class="font-medium text-slate-700">Tidak ada antrian yang sesuai filter.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($queues->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $queues->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function updateLiveStats() {
        fetch('{{ route("admin.api.monitor") }}')
            .then(r => r.json())
            .then(data => {
                const waiting    = data.queues.filter(q => q.status === 'waiting').length;
                const inProgress = data.queues.filter(q => q.status === 'in_progress' || q.status === 'serving').length;

                document.getElementById('live-waiting').textContent = waiting;
                document.getElementById('live-serving').textContent = inProgress;
                document.getElementById('live-time').textContent    = data.last_updated;
            })
            .catch(() => {});
    }

    updateLiveStats();
    setInterval(updateLiveStats, 5000);
</script>
@endpush
