@extends('layouts.app')

@section('title', 'Status Antrian #' . $queue->formatted_queue_number)

@section('content')
<div class="max-w-sm mx-auto px-4 py-8">

    {{-- Back Button --}}
    <a href="{{ route('branch.detail', $branch->code) }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 transition-colors text-sm mb-6 font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke {{ $branch->name }}
    </a>

    {{-- Queue Number Display --}}
    <div class="bg-white border-2 border-dashed border-slate-200 rounded-3xl p-8 text-center mb-5 shadow-sm relative overflow-hidden">
        <!-- Ticket Cutouts -->
        <div class="absolute -left-4 top-1/2 w-8 h-8 bg-slate-50 rounded-full transform -translate-y-1/2 border-r border-slate-200"></div>
        <div class="absolute -right-4 top-1/2 w-8 h-8 bg-slate-50 rounded-full transform -translate-y-1/2 border-l border-slate-200"></div>

        <div class="text-xs text-amber-600 font-bold tracking-wider uppercase mb-4">Nomor Antrian Anda</div>
        <div id="queue-number" class="text-7xl font-black text-slate-900 mb-2 tracking-tighter">
            {{ $queue->formatted_queue_number }}
        </div>
        <div class="text-slate-500 text-sm mt-2 font-medium">{{ $branch->name }}</div>

        {{-- Status Badge --}}
        <div class="mt-5">
            <span id="status-badge" class="inline-block px-5 py-2 rounded-full text-sm font-bold shadow-sm
                {{ $queue->status === 'waiting'      ? 'bg-amber-50 text-amber-600 border border-amber-200' : '' }}
                {{ in_array($queue->status, ['in_progress', 'serving']) ? 'bg-blue-50 text-blue-600 border border-blue-200 animate-pulse' : '' }}
                {{ in_array($queue->status, ['completed', 'done'])      ? 'bg-green-50 text-green-600 border border-green-200' : '' }}
                {{ $queue->status === 'timeout'      ? 'bg-red-50 text-red-600 border border-red-200' : '' }}
                {{ $queue->status === 'cancelled'    ? 'bg-slate-100 text-slate-500 border border-slate-200' : '' }}
            ">
                {{ $queue->status_label }}
            </span>
        </div>
    </div>

    {{-- Stats --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-5 shadow-sm">
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center">
                <div id="position" class="text-4xl font-black text-slate-900">{{ $queue->position }}</div>
                <div class="text-xs text-slate-500 mt-1 font-medium">Posisi dalam antrian</div>
            </div>
            <div class="text-center border-l border-slate-100">
                <div id="estimated-wait" class="text-4xl font-black text-amber-600">
                    {{ $queue->estimated_wait }}
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">Menit estimasi</div>
            </div>
        </div>

        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
            <div>
                <div class="text-xs text-slate-500 font-medium">Waktu masuk antrian</div>
                <div class="text-sm font-bold text-slate-800">{{ $queue->joined_at->format('H:i') }}</div>
            </div>
            <div class="text-right">
                <div class="text-xs text-slate-500 font-medium" id="last-updated">Auto refresh aktif</div>
                <div class="flex items-center gap-1.5 justify-end mt-1">
                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-xs text-green-600 font-bold">Live</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Customer QR Tiket --}}
    @if($queue->queue_qr && isset($customerQrSvg))
    <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-5 shadow-sm text-center">
        <div class="text-xs text-slate-500 font-bold tracking-wider uppercase mb-3">🎫 QR Tiket Anda</div>
        <div class="inline-block bg-white border border-slate-100 rounded-xl p-3 shadow-sm mb-3">
            {!! $customerQrSvg !!}
        </div>
        <p class="text-xs text-slate-500 font-medium">Tunjukkan QR ini ke kasir/admin barbershop saat dipanggil.</p>
    </div>
    @endif

    {{-- Tip --}}
    @if(in_array($queue->status, ['waiting', 'in_progress', 'serving']))
    <div class="bg-amber-50 border border-amber-200 p-4 rounded-2xl mb-5 shadow-sm">
        <div class="flex items-start gap-3">
            <span class="text-xl shrink-0">💡</span>
            <div>
                <p class="text-xs font-bold text-amber-700 mb-1">Tips</p>
                <p class="text-xs text-slate-600 leading-relaxed font-medium">
                    Halaman ini update otomatis setiap 10 detik. Datanglah ke barbershop sekitar
                    <strong class="text-amber-600">{{ max(0, $queue->estimated_wait - $branch->avg_service_minutes) }} menit</strong> lagi.
                </p>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="space-y-3">
        <a href="{{ route('branch.detail', $branch->code) }}"
           class="btn-primary block w-full py-3.5 rounded-xl text-center text-sm shadow-sm">
            Lihat Semua Antrian
        </a>
        <a href="{{ route('queue.leave', $branch->code) }}"
           class="block w-full py-3.5 rounded-xl text-center text-sm text-red-600 font-bold border border-red-200 bg-white hover:bg-red-50 hover:border-red-300 transition-colors shadow-sm">
            Keluar dari Antrian
        </a>
    </div>
    @else
    <div class="text-center">
        <a href="{{ route('branch.detail', $branch->code) }}"
           class="btn-primary inline-block px-8 py-3.5 rounded-xl text-sm shadow-sm">
            Kembali ke Halaman Cabang
        </a>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    @if(in_array($queue->status, ['waiting', 'in_progress', 'serving']))
    const branchCode = '{{ $branch->code }}';
    const token = '{{ $token }}';

    function updateStatus() {
        fetch(`/api/queue/status/${branchCode}/${token}`)
            .then(r => r.json())
            .then(data => {
                if (!data.found) return;

                document.getElementById('position').textContent      = data.position;
                document.getElementById('estimated-wait').textContent = data.estimated_wait;
                document.getElementById('last-updated').textContent   = 'Update: ' + data.last_updated;

                const badge = document.getElementById('status-badge');
                if (badge) {
                    badge.textContent = data.status_label;
                    badge.className   = 'inline-block px-5 py-2 rounded-full text-sm font-bold shadow-sm ';
                    if (data.status === 'waiting') badge.className += 'bg-amber-50 text-amber-600 border border-amber-200';
                    else if (['in_progress', 'serving'].includes(data.status)) badge.className += 'bg-blue-50 text-blue-600 border border-blue-200 animate-pulse';
                    else if (['completed', 'done'].includes(data.status))      badge.className += 'bg-green-50 text-green-600 border border-green-200';
                    else if (['timeout','cancelled'].includes(data.status))    badge.className += 'bg-red-50 text-red-600 border border-red-200';

                    if (['completed', 'done', 'timeout', 'cancelled'].includes(data.status)) {
                        clearInterval(polling);
                    }
                }
            })
            .catch(() => {});
    }

    const polling = setInterval(updateStatus, 10000);
    @endif
</script>
@endpush
