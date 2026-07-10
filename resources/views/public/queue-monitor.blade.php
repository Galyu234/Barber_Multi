@extends('layouts.app')

@section('title', $branch->name . ' — Monitor Antrian Real-time')

@push('styles')
<style>
    .live-dot {
        display: inline-block;
        width: 9px; height: 9px;
        background: #22c55e;
        border-radius: 50%;
        animation: pulse 1.8s cubic-bezier(0.4,0,0.6,1) infinite;
    }
    .queue-row { transition: all 0.4s ease; }
    .queue-row.is-serving {
        background: linear-gradient(90deg, #eff6ff 0%, #dbeafe 100%);
        border-color: #93c5fd !important;
    }
    .number-badge {
        font-feature-settings: "tnum";
        font-variant-numeric: tabular-nums;
    }
    .status-bar-sepi   { background: linear-gradient(90deg,#dcfce7,#bbf7d0); }
    .status-bar-sedang { background: linear-gradient(90deg,#fef9c3,#fef08a); }
    .status-bar-ramai  { background: linear-gradient(90deg,#fee2e2,#fecaca); }
    .guest-lock-banner {
        background: linear-gradient(135deg,#fef3c7,#fde68a);
        border: 1.5px solid #f59e0b;
    }
    .stat-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 1rem; }
    .empty-state { border: 2px dashed #e2e8f0; }
    @keyframes slideIn { from { opacity:0; transform: translateY(12px); } to { opacity:1; transform: translateY(0); } }
    .slide-in { animation: slideIn 0.4s ease-out; }
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">

    {{-- ── Back Navigation ──────────────────────────────────────── --}}
    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-amber-600 font-semibold mb-5 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Daftar Cabang
    </a>

    {{-- ── Branch Header ─────────────────────────────────────────── --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-4 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start gap-4">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
                @if($branch->barbershop->logo ?? null)
                    <img src="{{ asset('storage/' . $branch->barbershop->logo) }}" class="w-full h-full object-cover">
                @else
                    <span class="text-amber-600 text-3xl font-black">✂</span>
                @endif
            </div>
            <div class="flex-1">
                <div class="text-xs text-amber-600 font-bold uppercase tracking-wider mb-0.5">{{ $branch->barbershop->name ?? 'Barbershop' }}</div>
                <h1 class="text-2xl font-black text-slate-900 leading-tight">{{ $branch->name }}</h1>
                <div class="flex items-center flex-wrap gap-2 mt-3">
                    {{-- Open/Closed --}}
                    @if($branch->isOpen())
                        <span class="text-xs font-bold bg-green-100 text-green-700 border border-green-200 px-2.5 py-1 rounded-full">● Buka</span>
                    @else
                        <span class="text-xs font-bold bg-red-100 text-red-600 border border-red-200 px-2.5 py-1 rounded-full">● Tutup</span>
                    @endif
                    {{-- Jam --}}
                    <span class="text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200 px-2.5 py-1 rounded-full">
                        🕐 {{ substr($branch->open_time,0,5) }} – {{ substr($branch->close_time,0,5) }}
                    </span>
                    {{-- Status keramaian --}}
                    @php
                        $qCount = $queues->count();
                        $status = $qCount <= 2 ? 'sepi' : ($qCount <= 6 ? 'sedang' : 'ramai');
                        $labels = ['sepi'=>'🟢 Sepi','sedang'=>'🟡 Sedang','ramai'=>'🔴 Ramai'];
                        $statusBg = ['sepi'=>'status-sepi','sedang'=>'status-sedang','ramai'=>'status-ramai'];
                    @endphp
                    <span id="branch-status-badge" class="{{ $statusBg[$status] }} text-xs font-bold px-2.5 py-1 rounded-full">
                        {{ $labels[$status] }}
                    </span>
                </div>

                @if($branch->address)
                <div class="flex items-start gap-1.5 text-xs text-slate-500 mt-3">
                    <svg class="w-3.5 h-3.5 mt-0.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ $branch->address }}
                </div>
                @endif
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="grid grid-cols-3 gap-3 mt-5 pt-4 border-t border-slate-100">
            <div class="stat-card p-4 text-center">
                <div id="queue-count" class="text-3xl font-black text-slate-800">{{ $qCount }}</div>
                <div class="text-xs text-slate-500 mt-1 font-medium">Mengantri</div>
            </div>
            <div class="stat-card p-4 text-center">
                <div id="est-wait" class="text-3xl font-black text-amber-600">
                    {{ $qCount * $branch->avg_service_minutes }}
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">Menit tunggu</div>
            </div>
            <div class="stat-card p-4 text-center">
                <div class="text-3xl font-black text-blue-600">{{ $branch->avg_service_minutes }}</div>
                <div class="text-xs text-slate-500 mt-1 font-medium">Mnt/orang</div>
            </div>
        </div>
    </div>

    {{-- ── Guest Lock Banner — View Only ────────────────────────── --}}
    <div class="guest-lock-banner rounded-2xl p-4 mb-5 flex items-start gap-3 shadow-sm">
        <span class="text-2xl mt-0.5">👁</span>
        <div>
            <h4 class="font-bold text-amber-900 text-sm">Mode Lihat Saja</h4>
            <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                Kamu sedang melihat antrian <strong>{{ $branch->name }}</strong> secara real-time.
                Untuk masuk antrian, datang langsung ke cabang dan <strong>scan QR Code</strong> di kasir.
            </p>
        </div>
    </div>

    {{-- ── Queue List ─────────────────────────────────────────────── --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-slate-900 flex items-center gap-2">
                Daftar Antrian Saat Ini
                <span class="live-dot"></span>
            </h2>
            <div class="text-xs text-slate-500 font-medium">
                Update: <span id="last-updated-time">--:--:--</span>
            </div>
        </div>

        <div id="queue-list">
            @if($queues->isEmpty())
            <div class="empty-state text-center py-12 rounded-xl">
                <div class="text-5xl mb-3">🎉</div>
                <p class="text-slate-700 font-bold">Tidak ada antrian saat ini!</p>
                <p class="text-slate-500 text-sm mt-1">Cabang ini sangat sepi — datang sekarang dan langsung dilayani.</p>
            </div>
            @else
                @foreach($queues as $i => $q)
                <div class="queue-row flex items-center gap-3 py-3 px-2 rounded-xl border border-transparent mb-1 {{ in_array($q->status, ['in_progress','serving']) ? 'is-serving' : 'hover:bg-slate-50' }} transition-all">
                    {{-- Number --}}
                    <div class="number-badge w-12 h-12 rounded-xl shrink-0 flex items-center justify-center font-black text-base border
                        {{ in_array($q->status, ['in_progress','serving']) ? 'bg-blue-100 border-blue-300 text-blue-700 shadow-inner' : 'bg-slate-100 border-slate-200 text-slate-600' }}">
                        {{ $q->formatted_queue_number }}
                    </div>
                    {{-- Info --}}
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-bold {{ in_array($q->status, ['in_progress','serving']) ? 'text-blue-900' : 'text-slate-900' }}">
                                Nomor {{ $q->formatted_queue_number }}
                            </span>
                            @if(in_array($q->status, ['in_progress','serving']))
                            <span class="bg-blue-50 text-blue-600 text-xs px-2 py-0.5 rounded-full border border-blue-200 animate-pulse font-semibold">✂ Sedang Dicukur</span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-500 font-medium mt-0.5">Masuk: {{ $q->joined_at->format('H:i') }}</div>
                    </div>
                    {{-- Position --}}
                    <div class="text-right shrink-0">
                        <div class="text-xs text-slate-400 font-medium">Posisi</div>
                        <div class="text-xl font-black text-slate-800">{{ $i + 1 }}</div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- ── CTA: Scan QR ───────────────────────────────────────────── --}}
    <div class="bg-slate-900 text-white rounded-2xl p-6 text-center mb-6">
        <div class="text-3xl mb-3">📲</div>
        <h3 class="font-black text-lg mb-2">Siap Cukur di Sini?</h3>
        <p class="text-slate-400 text-sm mb-4 leading-relaxed">
            Datang ke <strong class="text-amber-400">{{ $branch->name }}</strong> dan scan QR Code di kasir untuk masuk antrian secara otomatis.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <div class="inline-flex items-center gap-2 text-sm font-bold bg-amber-500 text-white px-5 py-3 rounded-xl cursor-default select-none">
                ✂️ Scan QR di Kasir untuk Join
            </div>
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 text-sm font-semibold text-slate-300 border border-slate-600 px-5 py-3 rounded-xl hover:border-slate-400 transition-colors">
                🗺 Cabang Lain
            </a>
        </div>
    </div>

    {{-- ── Info Tips ───────────────────────────────────────────────── --}}
    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-sm text-blue-700 flex items-start gap-3">
        <span class="text-xl mt-0.5">💡</span>
        <div>
            <strong>Tips:</strong> Halaman ini diperbarui otomatis setiap <strong>5 detik</strong>.
            Kamu bisa buka halaman ini dari HP sambil perjalanan menuju barbershop untuk memantau posisi antrian.
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const branchCode = '{{ $branch->code }}';

    function updateQueue() {
        fetch(`/api/branch/${branchCode}/queue`)
            .then(r => r.json())
            .then(data => {
                // Update stats
                document.getElementById('queue-count').textContent = data.branch.queue_count;
                document.getElementById('est-wait').textContent    = data.branch.estimated_wait;
                document.getElementById('last-updated-time').textContent = data.last_updated;

                // Update status badge
                const badge = document.getElementById('branch-status-badge');
                if (badge) {
                    const icons = { sepi: '🟢 Sepi', sedang: '🟡 Sedang', ramai: '🔴 Ramai' };
                    const cls   = { sepi: 'status-sepi', sedang: 'status-sedang', ramai: 'status-ramai' };
                    badge.textContent = icons[data.branch.queue_status] || '';
                    badge.className   = `${cls[data.branch.queue_status] || ''} text-xs font-bold px-2.5 py-1 rounded-full`;
                }

                // Update queue list
                const list = document.getElementById('queue-list');
                let html = '';
                if (data.queues.length === 0) {
                    html = `<div class="empty-state text-center py-12 rounded-xl">
                        <div class="text-5xl mb-3">🎉</div>
                        <p class="text-slate-700 font-bold">Tidak ada antrian saat ini!</p>
                        <p class="text-slate-500 text-sm mt-1">Cabang ini sangat sepi — datang sekarang dan langsung dilayani.</p>
                    </div>`;
                } else {
                    html = data.queues.map((q, i) => {
                        const isServing = ['in_progress','serving'].includes(q.status);
                        return `
                        <div class="queue-row flex items-center gap-3 py-3 px-2 rounded-xl border border-transparent mb-1 ${isServing ? 'is-serving' : 'hover:bg-slate-50'} transition-all slide-in">
                            <div class="number-badge w-12 h-12 rounded-xl shrink-0 flex items-center justify-center font-black text-base border
                                ${isServing ? 'bg-blue-100 border-blue-300 text-blue-700 shadow-inner' : 'bg-slate-100 border-slate-200 text-slate-600'}">
                                ${q.queue_number}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-bold ${isServing ? 'text-blue-900' : 'text-slate-900'}">
                                        Nomor ${q.queue_number}
                                    </span>
                                    ${isServing ? '<span class="bg-blue-50 text-blue-600 text-xs px-2 py-0.5 rounded-full border border-blue-200 animate-pulse font-semibold">✂ Sedang Dicukur</span>' : ''}
                                </div>
                                <div class="text-xs text-slate-500 font-medium mt-0.5">Masuk: ${q.joined_at}</div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-xs text-slate-400 font-medium">Posisi</div>
                                <div class="text-xl font-black text-slate-800">${i + 1}</div>
                            </div>
                        </div>`;
                    }).join('');
                }

                if (list.innerHTML !== html) {
                    list.innerHTML = html;
                }
            })
            .catch(() => {});
    }

    setInterval(updateQueue, 5000);
    document.getElementById('last-updated-time').textContent = new Date().toLocaleTimeString('id-ID');
</script>
@endpush
