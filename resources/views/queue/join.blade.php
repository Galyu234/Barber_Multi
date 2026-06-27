@extends('layouts.app')

@section('title', 'Masuk Antrian — ' . $branch->name)

@section('content')
<div class="max-w-sm mx-auto px-4 py-8">

    {{-- Back Button --}}
    <a href="{{ route('branch.detail', $branch->code) }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 transition-colors text-sm mb-6 font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke {{ $branch->name }}
    </a>

    {{-- Icon Header --}}
    <div class="text-center mb-6">
        <div class="w-20 h-20 mx-auto rounded-3xl bg-amber-50 border border-amber-200 flex items-center justify-center text-4xl mb-4 shadow-sm">📲</div>
        <div class="text-xs text-amber-600 font-bold tracking-wider uppercase mb-2">Masuk Antrian</div>
        <h1 class="text-2xl font-black text-slate-900">{{ $branch->name }}</h1>
        <p class="text-slate-500 text-sm mt-1 font-medium">{{ $branch->barbershop->name }}</p>
    </div>

    @if($alreadyInQueue)
    {{-- Already in queue — show ticket --}}
    <div class="bg-white border border-amber-200 rounded-2xl p-6 text-center mb-4 shadow-sm">
        <div class="text-5xl font-black text-amber-600 mb-2">
            #{{ str_pad($alreadyInQueue->queue_number, 3, '0', STR_PAD_LEFT) }}
        </div>
        <p class="text-slate-700 font-bold mb-1">Nomor Antrian Anda</p>
        <p class="text-slate-500 text-sm font-medium">Anda sudah berada dalam antrian ini.</p>

        <div class="grid grid-cols-2 gap-3 mt-5 pt-4 border-t border-slate-100">
            <div>
                <div class="text-2xl font-black text-slate-900">{{ $alreadyInQueue->position }}</div>
                <div class="text-xs text-slate-500 font-medium mt-1">Posisi</div>
            </div>
            <div>
                <div class="text-2xl font-black text-amber-600">{{ $alreadyInQueue->estimated_wait }}m</div>
                <div class="text-xs text-slate-500 font-medium mt-1">Estimasi tunggu</div>
            </div>
        </div>

        {{-- Customer QR Tiket --}}
        @if($alreadyInQueue->queue_qr && isset($customerQrSvg))
        <div class="mt-5 pt-5 border-t border-slate-100">
            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-3">🎫 QR Tiket Anda</p>
            <div class="inline-block bg-white border border-slate-100 rounded-xl p-3 shadow-sm">
                {!! $customerQrSvg !!}
            </div>
            <p class="text-xs text-slate-500 mt-2 font-medium">Tunjukkan QR ini ke admin saat dipanggil.</p>
        </div>
        @endif

        <a href="{{ route('queue.status', [$branch->code, $alreadyInQueue->queue_qr ?? $alreadyInQueue->customer_session]) }}"
           class="btn-primary block w-full py-3.5 rounded-xl text-center mt-5 text-sm shadow-sm">
            Lihat Status Antrian
        </a>
    </div>

    <a href="{{ route('queue.leave', $branch->code) }}"
       class="block w-full py-3.5 rounded-xl text-center text-sm text-red-600 font-bold border border-red-200 bg-white hover:bg-red-50 hover:border-red-300 transition-colors shadow-sm">
        Keluar dari Antrian
    </a>

    @else
    {{-- Confirm Join --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-4 shadow-sm">
        <div class="space-y-4 mb-6">
            @php
                $activeCount   = $branch->activeQueues()->count();
                $estimatedWait = $activeCount * $branch->avg_service_minutes;
            @endphp

            <div class="flex justify-between items-center py-2.5 border-b border-slate-100">
                <span class="text-sm text-slate-500 font-medium">Cabang</span>
                <span class="text-sm font-bold text-slate-800">{{ $branch->code }}</span>
            </div>
            <div class="flex justify-between items-center py-2.5 border-b border-slate-100">
                <span class="text-sm text-slate-500 font-medium">Antrian saat ini</span>
                <span class="text-sm font-bold text-slate-900">{{ $activeCount }} orang</span>
            </div>
            <div class="flex justify-between items-center py-2.5 border-b border-slate-100">
                <span class="text-sm text-slate-500 font-medium">Estimasi tunggu</span>
                <span class="text-sm font-bold text-amber-600">
                    {{ $activeCount === 0 ? 'Langsung dilayani!' : '±' . $estimatedWait . ' menit' }}
                </span>
            </div>
            <div class="flex justify-between items-center py-2.5">
                <span class="text-sm text-slate-500 font-medium">Status cabang</span>
                <span class="status-{{ $branch->queue_status }} text-xs px-3 py-1 rounded-full font-bold">
                    {{ $branch->queue_status_label }}
                </span>
            </div>
        </div>

        <p class="text-xs text-slate-500 text-center mb-5 font-medium leading-relaxed">
            Dengan menekan tombol di bawah, Anda akan mendapatkan nomor antrian dan QR tiket pribadi.
        </p>

        <form action="{{ route('queue.join.post', $branch->code) }}" method="POST">
            @csrf
            <button type="submit"
                    class="btn-primary w-full py-4 rounded-xl text-base font-bold tracking-wide hover:scale-[1.02] active:scale-[0.98] shadow-md">
                ✅ Masuk Antrian Sekarang
            </button>
        </form>
    </div>

    <a href="{{ route('branch.detail', $branch->code) }}"
       class="block w-full py-3 rounded-xl text-center text-sm font-semibold text-slate-500 hover:text-slate-800 transition-colors">
        ← Lihat Daftar Antrian
    </a>
    @endif
</div>
@endsection
