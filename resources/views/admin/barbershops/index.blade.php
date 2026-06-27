@extends('layouts.admin')

@section('title', 'Barbershop')
@section('page-title', 'Manajemen Barbershop')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-bold text-slate-800">Daftar Barbershop</h2>
        <p class="text-xs text-slate-500 mt-0.5">Total {{ $barbershops->total() }} barbershop terdaftar</p>
    </div>
</div>

<div class="admin-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[768px]">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/50">
                    <th class="text-left py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider rounded-tl-lg">Barbershop</th>
                    <th class="text-left py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider hidden md:table-cell">Pemilik</th>
                    <th class="text-center py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider hidden sm:table-cell">Cabang</th>
                    <th class="text-center py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider">Status</th>
                    <th class="text-right py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider rounded-tr-lg">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($barbershops as $shop)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-4 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 font-bold text-sm shrink-0">
                                {{ strtoupper(substr($shop->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-bold text-slate-900">{{ $shop->name }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $shop->phone ?? '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-5 text-slate-600 font-medium hidden md:table-cell">{{ $shop->owner_name }}</td>
                    <td class="py-4 px-5 text-center hidden sm:table-cell">
                        <span class="badge badge-blue">{{ $shop->branches_count }} cabang</span>
                    </td>
                    <td class="py-4 px-5 text-center">
                        @if($shop->is_active)
                            <span class="badge badge-green">✅ Aktif</span>
                        @else
                            <span class="badge badge-red">🔴 Suspended</span>
                        @endif
                    </td>
                    <td class="py-4 px-5">
                        <div class="flex items-center justify-end gap-2 flex-wrap">
                            <a href="{{ route('admin.barbershops.show', $shop) }}"
                               class="text-xs text-blue-600 hover:text-blue-700 px-3 py-1.5 rounded-lg border border-blue-200 hover:bg-blue-50 font-semibold transition-all">
                                Detail
                            </a>
                            <a href="{{ route('admin.barbershops.edit', $shop) }}"
                               class="text-xs text-slate-600 hover:text-slate-700 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 font-semibold transition-all">
                                Edit
                            </a>
                            {{-- Toggle Suspend / Aktifkan --}}
                            <form action="{{ route('admin.barbershops.toggle-suspend', $shop) }}" method="POST"
                                  onsubmit="return confirm('{{ $shop->is_active ? 'Suspend barbershop ini? Semua cabang akan dinonaktifkan.' : 'Aktifkan kembali barbershop ini?' }}')">
                                @csrf
                                <button type="submit"
                                    class="text-xs px-3 py-1.5 rounded-lg border transition-all font-semibold
                                        {{ $shop->is_active
                                            ? 'text-orange-400 border-orange-500/25 hover:bg-orange-500/10'
                                            : 'text-green-400 border-green-500/25 hover:bg-green-500/10' }}">
                                    {{ $shop->is_active ? '⏸ Suspend' : '▶ Aktifkan' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.barbershops.destroy', $shop) }}" method="POST"
                                  onsubmit="return confirm('Hapus barbershop ini? Semua cabang juga akan terhapus!')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-16 text-center text-slate-400">
                        <div class="text-4xl mb-3">🏪</div>
                        <p class="font-medium text-slate-500">Belum ada barbershop terdaftar.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($barbershops->hasPages())
    <div class="px-5 py-4 border-t border-slate-200">
        {{ $barbershops->links() }}
    </div>
    @endif
</div>
@endsection
