@extends('layouts.admin')

@section('title', 'Tambah Cabang Baru')
@section('page-title', 'Tambah Cabang Baru')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('admin.branches.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-700 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Cabang
        </a>
    </div>

    <div class="admin-card p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-blue-500/15 border border-blue-500/20 flex items-center justify-center text-xl">📍</div>
            <div>
                <h2 class="font-bold text-slate-800 text-base">Tambah Cabang Baru</h2>
                <p class="text-xs text-slate-500 mt-0.5">Cabang untuk <span class="font-semibold text-blue-600">{{ auth()->user()->barbershop->name ?? 'Barbershop Anda' }}</span></p>
            </div>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-5">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.branches.store') }}" method="POST" class="space-y-5">
            @csrf


            {{-- Nama Cabang --}}
            <div>
                <label for="name" class="form-label">Nama Cabang <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       class="form-input" placeholder="cth: Rapih Selatan" required>
                <p class="text-xs text-slate-400 mt-1">Kode cabang & QR akan dibuat otomatis berdasarkan nama barbershop.</p>
            </div>

            {{-- Alamat --}}
            <div>
                <label for="address" class="form-label">Alamat</label>
                <textarea id="address" name="address" rows="2"
                          class="form-input resize-none" placeholder="Jl. Contoh No. 1, Kota...">{{ old('address') }}</textarea>
            </div>

            {{-- Telepon --}}
            <div>
                <label for="phone" class="form-label">Nomor Telepon</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                       class="form-input" placeholder="0812-xxxx-xxxx">
            </div>

            {{-- Jam Operasional --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="open_time" class="form-label">Jam Buka <span class="text-red-500">*</span></label>
                    <input type="time" id="open_time" name="open_time" value="{{ old('open_time', '08:00') }}"
                           class="form-input" required>
                </div>
                <div>
                    <label for="close_time" class="form-label">Jam Tutup <span class="text-red-500">*</span></label>
                    <input type="time" id="close_time" name="close_time" value="{{ old('close_time', '21:00') }}"
                           class="form-input" required>
                </div>
            </div>

            {{-- Durasi & Timeout --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="avg_service_minutes" class="form-label">Durasi Layanan (menit) <span class="text-red-500">*</span></label>
                    <input type="number" id="avg_service_minutes" name="avg_service_minutes"
                           value="{{ old('avg_service_minutes', 15) }}"
                           class="form-input" min="5" max="120" required>
                    <p class="text-xs text-slate-400 mt-1">Rata-rata waktu per pelanggan</p>
                </div>
                <div>
                    <label for="queue_timeout_minutes" class="form-label">Timeout Antrian (menit) <span class="text-red-500">*</span></label>
                    <input type="number" id="queue_timeout_minutes" name="queue_timeout_minutes"
                           value="{{ old('queue_timeout_minutes', 60) }}"
                           class="form-input" min="10" max="480" required>
                    <p class="text-xs text-slate-400 mt-1">Antrian otomatis dibatalkan setelah X menit</p>
                </div>
            </div>

            {{-- Auto-generate notice --}}
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <span class="text-blue-500 text-lg mt-0.5">ℹ️</span>
                    <div>
                        <p class="text-sm font-semibold text-blue-700">Dibuat Otomatis oleh Sistem</p>
                        <ul class="text-xs text-blue-600 mt-1 space-y-0.5 list-disc list-inside">
                            <li>Kode cabang unik (misal: RP002, RP003)</li>
                            <li>QR Code cabang siap diunduh</li>
                            <li>URL publik cabang aktif seketika</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 py-3 text-center font-bold">
                    ✅ Buat Cabang Baru
                </button>
                <a href="{{ route('admin.branches.index') }}"
                   class="btn-secondary flex-1 py-3 text-center font-semibold">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
