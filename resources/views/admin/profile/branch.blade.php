@extends('layouts.admin')

@section('title', 'Profil Cabang')
@section('page-title', 'Profil Cabang Saya')

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm mb-5 transition-colors font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Dashboard
    </a>
    <div class="admin-card p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-6">Informasi Barbershop &amp; Cabang</h2>


        <form action="{{ route('admin.profile.branch.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf @method('PUT')

            <!-- Barbershop Info -->
            <div>
                <h3 class="text-sm font-bold text-blue-600 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">Info Barbershop (Pusat)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Nama Barbershop <span class="text-red-500">*</span></label>
                        <input type="text" name="barbershop_name" value="{{ old('barbershop_name', $branch->barbershop->name) }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Nama Pemilik <span class="text-red-500">*</span></label>
                        <input type="text" name="owner_name" value="{{ old('owner_name', $branch->barbershop->owner_name) }}" class="form-input" required>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-5">
                    <div>
                        <label class="form-label">Nomor Telepon Pusat</label>
                        <input type="text" name="barbershop_phone" value="{{ old('barbershop_phone', $branch->barbershop->phone) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Logo Barbershop (kosongkan jika tidak diubah)</label>
                        @if($branch->barbershop->logo)
                            <img src="{{ asset('storage/' . $branch->barbershop->logo) }}" class="w-12 h-12 rounded-lg object-cover mb-2 border border-slate-200">
                        @endif
                        <input type="file" name="logo" accept="image/*" class="form-input py-1.5 text-sm">
                    </div>
                </div>

                <div class="mt-5">
                    <label class="form-label">Alamat Pusat</label>
                    <textarea name="barbershop_address" rows="2" class="form-input">{{ old('barbershop_address', $branch->barbershop->address) }}</textarea>
                </div>
            </div>

            <!-- Branch Info -->
            <div>
                <h3 class="text-sm font-bold text-amber-600 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">Info Cabang Saya</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Nama Cabang <span class="text-red-500">*</span></label>
                        <input type="text" name="branch_name" value="{{ old('branch_name', $branch->name) }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Kode Cabang (Read Only)</label>
                        <input type="text" value="{{ $branch->code }}" class="form-input bg-slate-50 text-slate-500" disabled>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-5">
                    <div>
                        <label class="form-label">Nomor Telepon Cabang</label>
                        <input type="text" name="branch_phone" value="{{ old('branch_phone', $branch->phone) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Rata-rata Waktu Layanan (menit) <span class="text-red-500">*</span></label>
                        <input type="number" name="avg_service_minutes" value="{{ old('avg_service_minutes', $branch->avg_service_minutes) }}" class="form-input" min="5" max="120" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5 mt-5">
                    <div>
                        <label class="form-label">Jam Buka <span class="text-red-500">*</span></label>
                        <input type="time" name="open_time" value="{{ old('open_time', substr($branch->open_time,0,5)) }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Jam Tutup <span class="text-red-500">*</span></label>
                        <input type="time" name="close_time" value="{{ old('close_time', substr($branch->close_time,0,5)) }}" class="form-input" required>
                    </div>
                </div>

                <div class="mt-5">
                    <label class="form-label">Alamat Cabang</label>
                    <textarea name="branch_address" rows="2" class="form-input">{{ old('branch_address', $branch->address) }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
