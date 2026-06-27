@extends('layouts.admin')

@section('title', 'Edit Cabang')
@section('page-title', 'Edit Cabang')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.branches.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm mb-6 transition-colors font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke daftar
    </a>

    <div class="admin-card p-6">
        <h2 class="text-base font-bold text-slate-800 mb-6">Edit: {{ $branch->name }}</h2>

        <form action="{{ route('admin.branches.update', $branch) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="form-label">Barbershop <span class="text-red-400">*</span></label>
                <select name="barbershop_id" class="form-input" required>
                    @foreach($barbershops as $shop)
                    <option value="{{ $shop->id }}" {{ old('barbershop_id', $branch->barbershop_id) == $shop->id ? 'selected' : '' }}>
                        {{ $shop->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Nama Cabang <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $branch->name) }}" class="form-input" required>
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Kode Cabang <span class="text-red-400">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $branch->code) }}" class="form-input" required
                           style="text-transform:uppercase" oninput="this.value = this.value.toUpperCase()">
                    @error('code')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Alamat Cabang</label>
                <textarea name="address" rows="2" class="form-input">{{ old('address', $branch->address) }}</textarea>
            </div>

            <div>
                <label class="form-label">Nomor Telepon Cabang</label>
                <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" class="form-input">
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Jam Buka</label>
                    <input type="time" name="open_time" value="{{ old('open_time', substr($branch->open_time,0,5)) }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Jam Tutup</label>
                    <input type="time" name="close_time" value="{{ old('close_time', substr($branch->close_time,0,5)) }}" class="form-input" required>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Timeout Antrian (menit)</label>
                    <input type="number" name="queue_timeout_minutes" value="{{ old('queue_timeout_minutes', $branch->queue_timeout_minutes) }}" class="form-input" min="10" max="480" required>
                </div>
                <div>
                    <label class="form-label">Rata-rata Waktu Layanan (menit)</label>
                    <input type="number" name="avg_service_minutes" value="{{ old('avg_service_minutes', $branch->avg_service_minutes) }}" class="form-input" min="5" max="120" required>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $branch->is_active) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-blue-500/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </label>
                <span class="text-sm text-slate-600">Cabang aktif</span>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Perbarui Cabang</button>
                <a href="{{ route('admin.branches.qrcode', $branch) }}" class="btn-secondary">Lihat QR Code</a>
                <a href="{{ route('admin.branches.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
