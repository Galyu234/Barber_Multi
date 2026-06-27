@extends('layouts.app')

@section('title', 'BarberQ — Platform Manajemen Antrian Barbershop')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12 md:py-20 text-center">

    {{-- Hero --}}
    <div class="mb-14">
        <div class="w-20 h-20 mx-auto rounded-3xl bg-amber-50 border border-amber-200 flex items-center justify-center text-4xl mb-6 shadow-sm">✂️</div>
        <h1 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight mb-4 tracking-tight">
            Antrian Barbershop<br><span style="background:linear-gradient(135deg,#f59e0b,#ea580c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Tanpa Ribet</span>
        </h1>
        <p class="text-slate-500 text-lg font-medium max-w-xl mx-auto mb-8 leading-relaxed">
            BarberQ — sistem antrian digital berbasis QR Code. Tidak perlu app, tidak perlu download, langsung jalan.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('register.barbershop') }}"
               class="w-full sm:w-auto px-8 py-4 rounded-xl text-base font-bold text-white shadow-lg hover:scale-[1.02] transition-all"
               style="background:linear-gradient(135deg,#f59e0b,#ea580c);">
                🏪 Daftar Barbershop — Gratis
            </a>
            <a href="{{ route('login') }}"
               class="w-full sm:w-auto px-8 py-4 rounded-xl text-base font-semibold text-slate-700 border border-slate-200 bg-white hover:bg-slate-50 transition-colors shadow-sm">
                Login Admin →
            </a>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-16">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="text-2xl md:text-3xl font-black text-slate-900">&lt; 1 mnt</div>
            <div class="text-xs md:text-sm text-slate-500 mt-1 font-medium">Waktu setup</div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="text-2xl md:text-3xl font-black text-slate-900">Gratis</div>
            <div class="text-xs md:text-sm text-slate-500 mt-1 font-medium">Tidak perlu bayar</div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="text-2xl md:text-3xl font-black text-slate-900">Live</div>
            <div class="text-xs md:text-sm text-slate-500 mt-1 font-medium">Update realtime</div>
        </div>
    </div>

    {{-- Cara Kerja --}}
    <div id="cara-kerja" class="mb-16">
        <div class="text-xs text-amber-600 font-bold tracking-wider uppercase mb-2">Untuk Pelanggan</div>
        <h2 class="text-2xl font-black text-slate-900 mb-8">3 Langkah Mudah</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach([
                ['📱','Scan QR Cabang','Scan QR yang terpasang di meja kasir barbershop.','blue'],
                ['🎫','Ambil Nomor Antrian','Sistem otomatis beri nomor & tiket digital Anda.','amber'],
                ['☕','Pantau dari HP','Lihat posisi antrian real-time. Datang tepat waktu.','green'],
            ] as [$icon, $title, $desc, $color])
            <div class="bg-white p-7 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-28 h-28 bg-{{ $color }}-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-60"></div>
                <div class="relative">
                    <div class="text-4xl mb-3">{{ $icon }}</div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">{{ $title }}</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Fitur untuk Pemilik --}}
    <div class="mb-16 bg-gradient-to-br from-amber-50 to-orange-50 rounded-3xl p-8 border border-amber-100">
        <div class="text-xs text-amber-600 font-bold tracking-wider uppercase mb-2">Untuk Pemilik Barbershop</div>
        <h2 class="text-2xl font-black text-slate-900 mb-6">Kelola Antrian Lebih Profesional</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-left mb-7">
            @foreach([
                ['✅','QR Code unik per cabang'],
                ['✅','Dashboard monitoring real-time'],
                ['✅','Scan tiket pelanggan'],
                ['✅','Download QR siap print'],
                ['✅','Statistik harian'],
                ['✅','Multi-cabang support'],
            ] as [$icon, $label])
            <div class="bg-white rounded-xl p-3 border border-amber-100 shadow-sm flex items-center gap-2">
                <span>{{ $icon }}</span>
                <span class="text-sm font-semibold text-slate-800">{{ $label }}</span>
            </div>
            @endforeach
        </div>
        <a href="{{ route('register.barbershop') }}"
           class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl text-base font-bold text-white shadow-lg hover:scale-[1.02] transition-all"
           style="background:linear-gradient(135deg,#f59e0b,#ea580c);">
            🚀 Daftar Sekarang — Gratis
        </a>
    </div>

    {{-- Bottom CTA --}}
    <div class="bg-slate-900 rounded-3xl p-8 text-white">
        <div class="text-3xl mb-3">✂️</div>
        <h2 class="text-xl font-black mb-2">Siap Modernisasi Antrian Barbershop?</h2>
        <p class="text-slate-400 text-sm mb-5">Setup dalam 1 menit. Tidak perlu install app.</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('register.barbershop') }}"
               class="w-full sm:w-auto px-7 py-3.5 rounded-xl font-bold text-white hover:scale-[1.02] transition-all"
               style="background:linear-gradient(135deg,#f59e0b,#ea580c);">
                🚀 Daftar Gratis
            </a>
            <a href="{{ route('login') }}"
               class="w-full sm:w-auto px-7 py-3.5 rounded-xl font-semibold text-slate-300 border border-slate-600 hover:border-slate-400 transition-colors text-sm">
                Sudah punya akun? Login →
            </a>
        </div>
    </div>
</div>
@endsection
