@extends('layouts.admin')

@section('title', 'Manajemen Cabang')
@section('page-title', 'Manajemen Cabang')

@section('content')
<div class="flex items-start justify-between mb-6">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-700 text-sm font-medium transition-colors mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Dashboard
        </a>
        <h2 class="text-lg font-bold text-slate-800">Daftar Cabang</h2>
        <p class="text-xs text-slate-500 mt-0.5 font-medium">Total {{ $branches->total() }} cabang</p>
    </div>
    @if(auth()->user()->isTenantAdmin())
    <a href="{{ route('admin.branches.create') }}"
       class="btn-primary flex items-center gap-2 mt-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Cabang
    </a>
    @endif
</div>


<div class="admin-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[768px]">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/50">
                    <th class="text-left py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider rounded-tl-lg">Cabang</th>
                    <th class="text-center py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider hidden md:table-cell">Antrian Aktif</th>
                    <th class="text-center py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider hidden lg:table-cell">Jam Operasional</th>
                    <th class="text-center py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider">Status</th>
                    <th class="text-right py-3.5 px-5 text-xs text-slate-500 font-bold uppercase tracking-wider rounded-tr-lg">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($branches as $branch)
                @php
                    $active = $branch->queues_count ?? 0;
                    $status = $active <= 2 ? 'sepi' : ($active <= 6 ? 'sedang' : 'ramai');
                    $badgeClass = ['sepi' => 'badge-green', 'sedang' => 'badge-yellow', 'ramai' => 'badge-red'][$status];
                @endphp
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-4 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs shrink-0">
                                {{ $branch->code }}
                            </div>
                            <div>
                                <div class="font-semibold text-slate-800">{{ $branch->name }}</div>
                                <div class="text-xs text-slate-500">{{ $branch->barbershop->name ?? '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-5 text-center hidden md:table-cell">
                        <span class="badge {{ $badgeClass }}">{{ $active }} orang</span>
                    </td>
                    <td class="py-4 px-5 text-center hidden lg:table-cell">
                        <span class="text-slate-500 font-medium text-xs">{{ substr($branch->open_time,0,5) }} – {{ substr($branch->close_time,0,5) }}</span>
                    </td>
                    <td class="py-4 px-5 text-center">
                        <span class="badge {{ $branch->is_active ? 'badge-green' : 'badge-gray' }}">
                            {{ $branch->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="py-4 px-5">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.branches.qrcode', $branch) }}"
                               class="text-xs text-amber-600 hover:text-amber-700 px-3 py-1.5 rounded-lg border border-amber-200 hover:bg-amber-50 font-semibold transition-all shadow-sm">
                                Generate QR
                            </a>
                            <a href="{{ route('admin.branches.edit', $branch) }}"
                               class="text-xs text-blue-600 hover:text-blue-700 px-3 py-1.5 rounded-lg border border-blue-200 hover:bg-blue-50 font-semibold transition-all shadow-sm">
                                Edit
                            </a>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isTenantAdmin())
                            <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST"
                                  onsubmit="return confirm('Hapus cabang ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-16 text-center text-slate-500">
                        <div class="text-4xl mb-3">📍</div>
                        <p class="font-medium">Belum ada cabang terdaftar.</p>
                        <p class="text-sm text-slate-400 mt-1">Cabang dibuat otomatis saat pemilik barbershop mendaftar.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($branches->hasPages())
    <div class="px-5 py-4 border-t border-slate-200">
        {{ $branches->links() }}
    </div>
    @endif
</div>
@endsection
