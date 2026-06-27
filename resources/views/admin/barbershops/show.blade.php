@extends('layouts.admin')

@section('title', 'Detail Barbershop')
@section('page-title', 'Detail Barbershop')

@section('content')
<div class="max-w-5xl mx-auto">
    <a href="{{ route('admin.barbershops.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm mb-6 transition-colors font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke daftar barbershop
    </a>

    <!-- Header Info -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-6 shadow-sm flex flex-col md:flex-row gap-6 items-start">
        <div class="w-24 h-24 rounded-2xl bg-slate-100 border border-slate-200 shrink-0 overflow-hidden flex items-center justify-center">
            @if($barbershop->logo)
                <img src="{{ asset('storage/' . $barbershop->logo) }}" class="w-full h-full object-cover">
            @else
                <span class="text-3xl text-slate-400 font-black">{{ strtoupper(substr($barbershop->name, 0, 1)) }}</span>
            @endif
        </div>
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-1">
                <h2 class="text-2xl font-black text-slate-900">{{ $barbershop->name }}</h2>
                @if($barbershop->is_active)
                    <span class="badge badge-green text-xs">Aktif</span>
                @else
                    <span class="badge badge-red text-xs">Suspended</span>
                @endif
            </div>
            <p class="text-slate-500 font-medium text-sm mb-4">Pemilik: <strong class="text-slate-800">{{ $barbershop->owner_name }}</strong></p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex items-start gap-2 text-slate-600">
                    <svg class="w-5 h-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>{{ $barbershop->phone ?? 'Belum ada nomor telepon' }}</span>
                </div>
                <div class="flex items-start gap-2 text-slate-600">
                    <svg class="w-5 h-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>{{ $barbershop->address ?? 'Belum ada alamat' }}</span>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.barbershops.edit', $barbershop) }}" class="btn-secondary">Edit Info</a>
        </div>
    </div>

    <!-- Data Tabs (Branches & Users) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Cabang List -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Cabang Barbershop ({{ $barbershop->branches->count() }})</h3>
            </div>
            <div class="p-5 flex-1 overflow-y-auto max-h-96">
                @if($barbershop->branches->isEmpty())
                    <p class="text-sm text-slate-500 text-center py-4">Belum ada cabang terdaftar.</p>
                @else
                    <div class="space-y-4">
                        @foreach($barbershop->branches as $branch)
                            <div class="flex items-start justify-between p-4 rounded-xl border border-slate-100 hover:bg-slate-50 transition-colors">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-bold text-slate-900">{{ $branch->name }}</span>
                                        <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-mono">{{ $branch->code }}</span>
                                        @if(!$branch->is_active)
                                            <span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded">Nonaktif</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 mb-2">
                                        {{ substr($branch->open_time,0,5) }} - {{ substr($branch->close_time,0,5) }} • {{ $branch->phone ?? '-' }}
                                    </div>
                                    @if($branch->active_queues_count > 0)
                                        <div class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-md">
                                            <div class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></div>
                                            {{ $branch->active_queues_count }} Antrian Aktif
                                        </div>
                                    @else
                                        <div class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 bg-slate-100 px-2 py-1 rounded-md">
                                            0 Antrian Aktif
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('admin.branches.qrcode', $branch) }}" class="text-xs font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-lg transition-colors border border-amber-200">
                                    QR Code
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Akun Admin List -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Akun Pengelola ({{ $barbershop->users->count() }})</h3>
            </div>
            <div class="p-5 flex-1 overflow-y-auto max-h-96">
                @if($barbershop->users->isEmpty())
                    <p class="text-sm text-slate-500 text-center py-4">Belum ada akun pengelola untuk barbershop ini.</p>
                @else
                    <div class="space-y-3">
                        @foreach($barbershop->users as $user)
                            <div class="flex items-center gap-4 p-3 rounded-xl border border-slate-100">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-900 text-sm truncate">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-500 truncate">{{ $user->email }}</div>
                                </div>
                                @if($user->branch)
                                    <div class="text-right shrink-0">
                                        <div class="text-[10px] text-slate-400 font-semibold uppercase">Cabang</div>
                                        <div class="text-xs font-medium text-slate-700 truncate max-w-[100px]">{{ $user->branch->name }}</div>
                                    </div>
                                @else
                                    <span class="badge badge-gray text-[10px]">Semua Cabang</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
