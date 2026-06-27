@extends('layouts.admin')

@section('title', 'Kelola User')
@section('page-title', 'Kelola User & Admin')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-base font-bold text-slate-800">Semua User</h2>
        <p class="text-sm text-slate-500 mt-0.5">Kelola akun admin dan super admin</p>
    </div>
</div>

<div class="admin-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[768px]">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/50">
                    <th class="text-left py-3 px-4 text-xs text-slate-500 font-bold uppercase tracking-wider rounded-tl-lg">Nama</th>
                    <th class="text-left py-3 px-4 text-xs text-slate-500 font-bold uppercase tracking-wider">Email</th>
                    <th class="text-center py-3 px-4 text-xs text-slate-500 font-bold uppercase tracking-wider">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Cabang (Tenant)</th>
                    <th class="text-center py-3 px-4 text-xs text-slate-500 font-bold uppercase tracking-wider rounded-tr-lg">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full {{ $user->isSuperAdmin() ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600' }} flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-slate-800">{{ $user->name }}</div>
                                @if($user->id === auth()->id())
                                    <div class="text-xs text-blue-500 font-medium">(Anda)</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-slate-600">{{ $user->email }}</td>
                    <td class="py-3.5 px-4 text-center">
                        @if($user->isSuperAdmin())
                            <span class="badge badge-blue">Super Admin</span>
                        @else
                            <span class="badge badge-gray">Admin</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-600">
                        @if($user->isSuperAdmin())
                            <span class="text-slate-400 italic">Akses Penuh</span>
                        @else
                            {{ $user->branch->name ?? '-' }} <br>
                            <span class="text-xs text-slate-400">{{ $user->branch->barbershop->name ?? '' }}</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="text-xs text-blue-600 hover:text-blue-800 font-semibold px-2.5 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 transition-colors">
                                Edit
                            </a>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                  onsubmit="return confirm('Hapus user {{ addslashes($user->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger text-xs py-1.5">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-12 text-center text-slate-400">
                        <div class="text-3xl mb-2">👤</div>
                        <div class="font-medium">Belum ada user</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-4 py-3 border-t border-slate-200">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
