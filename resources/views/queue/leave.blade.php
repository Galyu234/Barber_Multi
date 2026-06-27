@extends('layouts.app')

@section('title', 'Keluar Antrian — ' . $branch->name)

@section('content')
<div class="max-w-sm mx-auto px-4 py-10">
    <!-- Icon Header -->
    <div class="text-center mb-8">
        <div class="w-20 h-20 mx-auto rounded-3xl bg-red-50 border border-red-200 flex items-center justify-center text-4xl mb-4 shadow-sm">
            🚪
        </div>
        <div class="text-xs text-red-600 font-bold tracking-wider uppercase mb-2">Keluar Antrian</div>
        <h1 class="text-2xl font-black text-slate-900">{{ $branch->name }}</h1>
        <p class="text-slate-500 text-sm mt-1 font-medium">{{ $branch->barbershop->name }}</p>
    </div>

    @if(session('success'))
    <!-- Success State -->
    <div class="bg-white border border-green-200 rounded-2xl p-8 text-center mb-6 shadow-sm">
        <div class="text-6xl mb-4">🎉</div>
        <h2 class="text-xl font-bold text-green-600 mb-2">Terima Kasih!</h2>
        <p class="text-slate-600 text-sm font-medium">{{ session('success') }}</p>
        @if(session('queue_number'))
        <p class="text-slate-500 text-xs mt-3 font-medium">Nomor antrian #{{ str_pad(session('queue_number'), 3, '0', STR_PAD_LEFT) }} telah diselesaikan.</p>
        @endif

        <a href="{{ route('branch.detail', $branch->code) }}"
           class="btn-primary block w-full py-3.5 rounded-xl text-center mt-6 text-sm shadow-sm">
            Kembali ke Halaman Cabang
        </a>
    </div>

    @elseif($currentQueue)
    <!-- Has Active Queue -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-6 shadow-sm">
        <div class="text-center mb-6 pb-6 border-b border-slate-100">
            <div class="text-5xl font-black text-slate-900 mb-2">
                #{{ str_pad($currentQueue->queue_number, 3, '0', STR_PAD_LEFT) }}
            </div>
            <p class="text-slate-700 font-bold text-sm">Nomor antrian aktif Anda</p>
            <p class="text-xs text-slate-500 mt-1 font-medium">Masuk sejak {{ $currentQueue->joined_at->format('H:i') }}</p>
        </div>

        <p class="text-sm text-slate-500 text-center mb-6 font-medium leading-relaxed">
            Apakah Anda ingin keluar dari antrian? Tindakan ini tidak dapat dibatalkan.
        </p>

        <form action="{{ route('queue.leave.post', $branch->code) }}" method="POST">
            @csrf
            <button type="submit"
                    class="w-full py-4 rounded-xl text-base font-bold tracking-wide bg-red-50 border border-red-200 text-red-600 hover:bg-red-100 transition-all active:scale-[0.98] shadow-sm">
                ✅ Ya, Keluar dari Antrian
            </button>
        </form>
    </div>

    <a href="{{ route('branch.detail', $branch->code) }}"
       class="block w-full py-3 rounded-xl text-center text-sm font-semibold text-slate-500 hover:text-slate-800 transition-colors">
        ← Batal, Kembali
    </a>

    @else
    <!-- Not in Queue -->
    <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center mb-6 shadow-sm">
        <div class="text-6xl mb-4">❓</div>
        <h2 class="text-lg font-bold text-slate-900 mb-2">Anda Tidak Dalam Antrian</h2>
        <p class="text-slate-500 text-sm font-medium">Tidak ditemukan antrian aktif atas nama Anda di cabang ini.</p>

        @if(session('error'))
        <div class="mt-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm shadow-sm font-medium">
            {{ session('error') }}
        </div>
        @endif
    </div>

    <a href="{{ route('queue.join', $branch->code) }}"
       class="btn-primary block w-full py-3.5 rounded-xl text-center text-sm mb-3 shadow-sm">
        Masuk Antrian
    </a>
    <a href="{{ route('branch.detail', $branch->code) }}"
       class="block w-full py-3 rounded-xl text-center text-sm font-semibold text-slate-500 hover:text-slate-800 transition-colors">
        ← Kembali ke Halaman Cabang
    </a>
    @endif
</div>
@endsection
