@extends('layouts.app')

@section('title', $branch->name . ' — Antrian Real-time')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    {{-- Flow QR-Centric: Customer fokus pada antrian cabang yang mereka scan. --}}

    <!-- Branch Header -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-6 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start gap-5">
            <div class="w-20 h-20 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
                @if($branch->barbershop->logo)
                    <img src="{{ asset('storage/' . $branch->barbershop->logo) }}" class="w-full h-full object-cover">
                @else
                    <span class="text-amber-600 text-4xl font-black">✂</span>
                @endif
            </div>
            <div class="flex-1 w-full">
                <div class="text-xs text-blue-600 mb-1 font-bold uppercase tracking-wider">{{ $branch->barbershop->name }}</div>
                <h1 class="text-2xl font-black text-slate-900 leading-tight">{{ $branch->name }}</h1>
                
                <div class="mt-3 space-y-1.5 text-sm text-slate-600">
                    @if($branch->address)
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="leading-tight">{{ $branch->address }}</span>
                    </div>
                    @endif
                    @if($branch->phone)
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>{{ $branch->phone }}</span>
                    </div>
                    @endif
                </div>

                <div class="flex items-center gap-2.5 mt-4 flex-wrap">
                    <span id="branch-status-badge" class="status-{{ $branch->queue_status }} text-xs px-3 py-1.5 rounded-full font-bold">
                        {{ $branch->queue_status_label }}
                    </span>
                    @if($branch->isOpen())
                        <span class="text-green-700 text-xs font-bold bg-green-100 px-3 py-1.5 rounded-full border border-green-200">● Buka</span>
                    @else
                        <span class="text-red-700 text-xs font-bold bg-red-100 px-3 py-1.5 rounded-full border border-red-200">● Tutup</span>
                    @endif
                    <span class="text-slate-600 text-xs font-bold bg-slate-100 px-3 py-1.5 rounded-full border border-slate-200">
                        {{ substr($branch->open_time,0,5) }} – {{ substr($branch->close_time,0,5) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-3 gap-3 mt-6 pt-5 border-t border-slate-100">
            <div class="text-center">
                <div id="queue-count" class="text-3xl font-black text-slate-800">{{ $queues->count() }}</div>
                <div class="text-xs text-slate-500 mt-1 font-medium">Mengantri</div>
            </div>
            <div class="text-center border-x border-slate-100">
                <div id="estimated-wait" class="text-3xl font-black text-amber-600">
                    {{ $queues->count() * $branch->avg_service_minutes }}
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">Menit tunggu</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-black text-blue-600">{{ $branch->avg_service_minutes }}</div>
                <div class="text-xs text-slate-500 mt-1 font-medium">Mnt/orang</div>
            </div>
        </div>
    </div>

    <!-- My Queue Status -->
    @if($myQueue)
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 mb-6 shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-white rounded-xl shadow-sm border border-blue-100 flex items-center justify-center text-blue-600 font-black text-xl">
                {{ $myQueue->formatted_queue_number }}
            </div>
            <div>
                <h3 class="font-bold text-slate-900">Anda dalam antrian!</h3>
                <p class="text-sm text-slate-600">Posisi: <strong>{{ $myQueue->position }}</strong> • Est: <strong>{{ $myQueue->estimated_wait }} mnt</strong></p>
            </div>
        </div>
        <a href="{{ route('queue.status', ['branch_code' => $branch->code, 'token' => $myQueue->queue_qr ?? $myQueue->customer_session]) }}" class="bg-white border border-blue-200 text-blue-600 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-100 transition-colors">Lihat Detail</a>
    </div>
    @endif

    @php
        $scannedBranch = session('active_scanned_branch');
        $isReadOnly = $scannedBranch && $scannedBranch !== $branch->code;
    @endphp

    @if($isReadOnly)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 flex items-start gap-3 shadow-sm">
        <div class="text-amber-500 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
            <h4 class="font-bold text-amber-800 text-sm">Cabang Read Only</h4>
            <p class="text-xs text-amber-700 mt-1">Anda hanya dapat mengambil antrian di cabang yang pertama kali Anda scan (Cabang: <strong>{{ $scannedBranch }}</strong>).</p>
        </div>
    </div>
    @endif

    <!-- Queue List -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 mb-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-slate-900">Daftar Antrian Aktif</h2>
            <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
                <span id="last-updated-time"></span>
                <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
            </div>
        </div>

        <div id="queue-list" class="space-y-1">
            @if($queues->isEmpty())
            <div class="text-center py-10 bg-slate-50 rounded-xl border border-slate-100 border-dashed">
                <div class="text-4xl mb-3">🎉</div>
                <p class="text-slate-700 font-bold">Tidak ada antrian saat ini!</p>
                <p class="text-slate-500 text-sm mt-1">Tekan tombol Masuk Antrian untuk langsung dilayani.</p>
            </div>
            @else
                @foreach($queues as $i => $q)
                <div class="flex items-center gap-3 py-3 px-2 rounded-xl {{ $myQueue && $myQueue->id === $q->id ? 'bg-amber-50 border border-amber-200' : 'hover:bg-slate-50 border border-transparent' }} {{ in_array($q->status, ['in_progress', 'serving']) ? '!bg-blue-50/50 !border-blue-200 shadow-sm' : '' }} transition-colors">
                    <div class="w-11 h-11 rounded-xl {{ in_array($q->status, ['in_progress', 'serving']) ? 'bg-blue-100 border border-blue-200 text-blue-700 shadow-inner' : 'bg-slate-100 border border-slate-200 text-slate-600' }} flex items-center justify-center font-black text-base shrink-0">
                        {{ $q->formatted_queue_number }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold {{ in_array($q->status, ['in_progress', 'serving']) ? 'text-blue-900' : 'text-slate-900' }}">Nomor {{ $q->formatted_queue_number }} {!! $myQueue && $myQueue->id === $q->id ? '<span class="text-amber-600 ml-1">(Anda)</span>' : '' !!}</span>
                            @if(in_array($q->status, ['in_progress', 'serving']))
                            <span class="bg-blue-50 text-blue-600 text-xs px-2 py-0.5 rounded-full border border-blue-200 animate-pulse font-semibold">Sedang Dicukur</span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-500 font-medium">Masuk: {{ $q->joined_at->format('H:i') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-400 font-medium">Posisi</div>
                        <div class="text-lg font-black text-slate-800">{{ $i + 1 }}</div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-2 gap-4 mb-8">
        @if(!$myQueue)
            @if(!$isReadOnly)
            <form action="{{ route('queue.join.post', $branch->code) }}" method="POST" class="col-span-2 sm:col-span-1">
                @csrf
                <button type="submit" class="w-full flex flex-col items-center justify-center gap-2 p-4 rounded-2xl bg-amber-50 border border-amber-200 hover:bg-amber-100 hover:border-amber-300 transition-all text-center shadow-sm">
                    <span class="text-3xl">📲</span>
                    <span class="font-bold text-amber-700">Masuk Antrian</span>
                    <span class="text-xs text-amber-600 font-medium">Ambil nomor antrian</span>
                </button>
            </form>
            @else
            <div class="flex flex-col items-center justify-center gap-2 p-4 rounded-2xl bg-slate-100 border border-slate-200 text-center cursor-not-allowed opacity-60 col-span-2 sm:col-span-1">
                <span class="text-3xl">🔒</span>
                <span class="font-bold text-slate-500">Read Only</span>
                <span class="text-xs text-slate-400 font-medium">Tidak bisa ambil antrian</span>
            </div>
            @endif
        @else
        <a href="{{ route('queue.status', ['branch_code' => $branch->code, 'token' => $myQueue->queue_qr ?? $myQueue->customer_session]) }}"
           class="flex flex-col items-center justify-center gap-2 p-4 rounded-2xl bg-blue-50 border border-blue-200 hover:bg-blue-100 hover:border-blue-300 transition-all text-center shadow-sm col-span-2 sm:col-span-1">
            <span class="text-3xl">🎫</span>
            <span class="font-bold text-blue-700">Tiket Saya</span>
            <span class="text-xs text-blue-600 font-medium">Lihat status & kode</span>
        </a>
        @endif

        <a href="{{ route('queue.leave', $branch->code) }}"
           class="flex flex-col items-center justify-center gap-2 p-4 rounded-2xl bg-white border border-red-200 hover:bg-red-50 hover:border-red-300 transition-all text-center shadow-sm">
            <span class="text-3xl">🚪</span>
            <span class="font-bold text-red-600">Keluar Antrian</span>
            <span class="text-xs text-red-500 font-medium">Selesai / batalkan</span>
        </a>
    </div>

    <!-- Cabang Lainnya -->
    @if($otherBranches->isNotEmpty())
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center gap-2 mb-4">
            <h2 class="font-bold text-slate-900">🗺 Cabang Lainnya</h2>
            <span class="text-xs text-slate-400 font-medium">(Lihat saja — tidak bisa join)</span>
        </div>
        <div class="space-y-3">
            @foreach($otherBranches as $ob)
            @php
                $obCount  = $ob->active_queue_count ?? 0;
                $obStatus = $obCount <= 2 ? 'sepi' : ($obCount <= 6 ? 'sedang' : 'ramai');
                $obLabels = ['sepi' => '🟢 Sepi', 'sedang' => '🟡 Sedang', 'ramai' => '🔴 Ramai'];
            @endphp
            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 font-black text-xs shrink-0">
                        {{ $ob->code }}
                    </div>
                    <div>
                        <div class="font-semibold text-slate-900 text-sm">{{ $ob->name }}</div>
                        <div class="text-xs text-slate-500">{{ $ob->barbershop->name }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <div class="text-lg font-black text-slate-800">{{ $obCount }}</div>
                        <div class="text-xs text-slate-400">antrian</div>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full font-semibold status-{{ $obStatus }}">{{ $obLabels[$obStatus] }}</span>
                    <a href="{{ route('branch.detail', $ob->code) }}?from=list"
                       class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 font-semibold transition-colors">
                        Lihat
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        <p class="text-xs text-slate-400 mt-4 text-center">
            💡 Untuk masuk antrian cabang lain, scan QR code di cabang tersebut
        </p>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const branchCode = '{{ $branch->code }}';

    function updateQueueList() {
        fetch(`/api/branch/${branchCode}/queue`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('queue-count').textContent = data.branch.queue_count;
                document.getElementById('estimated-wait').textContent = data.branch.estimated_wait;
                document.getElementById('last-updated-time').textContent = data.last_updated;

                const badge = document.getElementById('branch-status-badge');
                if (badge) {
                    const icons = { sepi: '🟢 Sepi', sedang: '🟡 Sedang', ramai: '🔴 Ramai' };
                    badge.textContent = icons[data.branch.queue_status] || '';
                    badge.className = `status-${data.branch.queue_status} text-xs px-3 py-1 rounded-full font-semibold`;
                }

                const list = document.getElementById('queue-list');
                const myQueueId = {{ $myQueue ? $myQueue->id : 'null' }};
                let newHtml = '';
                if (data.queues.length === 0) {
                    newHtml = `<div class="text-center py-10 bg-slate-50 rounded-xl border border-slate-100 border-dashed"><div class="text-4xl mb-3">🎉</div><p class="text-slate-700 font-bold">Tidak ada antrian saat ini!</p><p class="text-slate-500 text-sm mt-1">Tekan tombol Masuk Antrian untuk langsung dilayani.</p></div>`;
                } else {
                    newHtml = data.queues.map((q, i) => {
                        const isMe = myQueueId === q.id;
                        return `
                        <div class="flex items-center gap-3 py-3 px-2 rounded-xl ${isMe ? 'bg-amber-50 border border-amber-200' : 'hover:bg-slate-50 border border-transparent'} ${['in_progress', 'serving'].includes(q.status) ? '!bg-blue-50/50 !border-blue-200 shadow-sm' : ''} transition-colors">
                            <div class="w-11 h-11 rounded-xl ${['in_progress', 'serving'].includes(q.status) ? 'bg-blue-100 border border-blue-200 text-blue-700 shadow-inner' : 'bg-slate-100 border border-slate-200 text-slate-600'} flex items-center justify-center font-black text-base shrink-0">
                                ${q.queue_number}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold ${['in_progress', 'serving'].includes(q.status) ? 'text-blue-900' : 'text-slate-900'}">Nomor ${q.queue_number} ${isMe ? '<span class="text-amber-600 ml-1">(Anda)</span>' : ''}</span>
                                    ${['in_progress', 'serving'].includes(q.status) ? '<span class="bg-blue-50 text-blue-600 text-xs px-2 py-0.5 rounded-full border border-blue-200 animate-pulse font-semibold">Sedang Dicukur</span>' : ''}
                                </div>
                                <div class="text-xs text-slate-500 font-medium">Masuk: ${q.joined_at}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-slate-400 font-medium">Posisi</div>
                                <div class="text-lg font-black text-slate-800">${i + 1}</div>
                            </div>
                        </div>
                    `}).join('');
                }
                
                if (list.innerHTML !== newHtml) {
                    list.innerHTML = newHtml;
                }
            })
            .catch(() => {});
    }

    setInterval(updateQueueList, 5000);
    document.getElementById('last-updated-time').textContent = new Date().toLocaleTimeString('id-ID');
</script>
@endpush
