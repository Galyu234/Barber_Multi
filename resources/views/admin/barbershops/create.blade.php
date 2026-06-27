@extends('layouts.admin')

@section('title', 'Tambah Barbershop')
@section('page-title', 'Tambah Barbershop')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.barbershops.index') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-300 text-sm mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke daftar
    </a>

    <div class="admin-card p-6">
        <h2 class="text-base font-bold text-gray-200 mb-6">Informasi Barbershop</h2>

        <form action="{{ route('admin.barbershops.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Nama Barbershop <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="Rapih Barbershop" required>
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Nama Pemilik <span class="text-red-400">*</span></label>
                    <input type="text" name="owner_name" value="{{ old('owner_name') }}" class="form-input" placeholder="Budi Santoso" required>
                    @error('owner_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Nomor Telepon</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="0812-3456-7890">
            </div>

            <div>
                <label class="form-label">Alamat</label>
                <textarea name="address" rows="3" class="form-input" placeholder="Jl. Merdeka No. 1, Jakarta">{{ old('address') }}</textarea>
            </div>

            <div>
                <label class="form-label">Logo (opsional)</label>
                <input type="file" name="logo" accept="image/*" class="form-input py-2">
            </div>

            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-700 peer-focus:ring-2 peer-focus:ring-blue-500/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </label>
                <span class="text-sm text-gray-400">Barbershop aktif</span>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Simpan Barbershop</button>
                <a href="{{ route('admin.barbershops.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
