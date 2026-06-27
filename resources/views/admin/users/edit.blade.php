@extends('layouts.admin')

@section('title', 'Edit User — ' . $user->name)
@section('page-title', 'Edit User')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm mb-6 transition-colors font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke daftar user
    </a>

    <div class="admin-card p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-full {{ $user->isSuperAdmin() ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600' }} flex items-center justify-center font-bold text-sm">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-800">{{ $user->name }}</h2>
                <p class="text-xs text-slate-500">{{ $user->email }}</p>
            </div>
        </div>

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required>
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Password Baru <span class="text-slate-400 font-normal">(kosongkan jika tidak diubah)</span></label>
                    <input type="password" name="password" class="form-input" placeholder="Min. 6 karakter">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi password baru">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Role <span class="text-red-500">*</span></label>
                    <select name="role" id="role-select" class="form-input" required onchange="toggleBranch(this.value)">
                        <option value="admin"       {{ old('role', $user->role) === 'admin'       ? 'selected' : '' }}>Admin Cabang</option>
                        <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                    @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div id="branch-field">
                    <label class="form-label">Cabang</label>
                    <select name="branch_id" class="form-input">
                        <option value="">— Pilih Cabang —</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id', $user->branch_id) == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->barbershop->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
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
