@extends('layouts.admin')

@section('title', 'Tambah User')
@section('page-title', 'Tambah User Baru')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm mb-6 transition-colors font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke daftar user
    </a>

    <div class="admin-card p-6">
        <h2 class="text-base font-bold text-slate-800 mb-6">Informasi User Baru</h2>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="Budi Santoso" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="admin@barbershop.com" required>
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" class="form-input" placeholder="Min. 6 karakter" required>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Konfirmasi Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi password" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Role <span class="text-red-500">*</span></label>
                    <select name="role" id="role-select" class="form-input" required onchange="toggleBranch(this.value)">
                        <option value="admin"       {{ old('role', 'admin') === 'admin'       ? 'selected' : '' }}>Admin Cabang</option>
                        <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                    @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div id="branch-field">
                    <label class="form-label">Cabang <span class="text-red-500">*</span></label>
                    <select name="branch_id" class="form-input">
                        <option value="">— Pilih Cabang —</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->barbershop->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-700">
                <strong>Catatan:</strong> Admin cabang hanya dapat melihat dan mengelola data milik cabangnya sendiri. Super admin memiliki akses penuh ke seluruh sistem.
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Simpan User</button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleBranch(role) {
        const field = document.getElementById('branch-field');
        field.style.display = role === 'super_admin' ? 'none' : 'block';
    }
    toggleBranch(document.getElementById('role-select').value);
</script>
@endpush
@endsection
