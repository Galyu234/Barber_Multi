@extends('layouts.app')

@section('title', 'BarberQ — Temukan Barber Terdekat & Cek Antrian Real-time')

@push('styles')
<style>
    .hero-bg {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        position: relative;
        overflow: hidden;
    }
    .hero-bg::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(ellipse at 30% 50%, rgba(245,158,11,0.12) 0%, transparent 60%),
                    radial-gradient(ellipse at 70% 30%, rgba(234,88,12,0.08) 0%, transparent 60%);
        animation: shimmer 8s ease-in-out infinite alternate;
    }
    @keyframes shimmer {
        0%   { transform: rotate(0deg) scale(1); }
        100% { transform: rotate(5deg) scale(1.05); }
    }
    .branch-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 1.25rem;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .branch-card:hover {
        border-color: #f59e0b;
        box-shadow: 0 12px 40px -8px rgba(245,158,11,0.18), 0 4px 16px -4px rgba(0,0,0,0.08);
        transform: translateY(-3px);
    }
    .branch-card.is-closed {
        opacity: 0.75;
        filter: grayscale(0.3);
    }
    .live-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
        animation: pulse 1.8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    .queue-badge-sepi   { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
    .queue-badge-sedang { background: #fef9c3; color: #ca8a04; border: 1px solid #fef08a; }
    .queue-badge-ramai  { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    .search-box {
        background: rgba(255,255,255,0.08);
        border: 1.5px solid rgba(255,255,255,0.15);
        backdrop-filter: blur(12px);
        border-radius: 1rem;
        color: #fff;
        transition: all 0.3s;
    }
    .search-box:focus {
        outline: none;
        background: rgba(255,255,255,0.12);
        border-color: rgba(245,158,11,0.6);
        box-shadow: 0 0 0 3px rgba(245,158,11,0.15);
    }
    .search-box::placeholder { color: rgba(255,255,255,0.45); }
    .filter-btn { background: rgba(255,255,255,0.08); border: 1.5px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.75); border-radius: 999px; transition: all 0.2s; }
    .filter-btn.active, .filter-btn:hover { background: #f59e0b; border-color: #f59e0b; color: #fff; }
    .skeleton { background: linear-gradient(90deg, #f0f4f8 25%, #e2e8f0 50%, #f0f4f8 75%); background-size: 200% 100%; animation: skeleton-wave 1.4s ease infinite; }
    @keyframes skeleton-wave { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
    .counter-num { animation: countUp 0.5s ease-out; }
    @keyframes countUp { from { opacity:0; transform:scale(0.8); } to { opacity:1; transform:scale(1); } }
    .no-branch-found { display: none; }
</style>
@endpush

@section('content')

{{-- ══════════════════ HERO SECTION ══════════════════ --}}
<section class="hero-bg text-white py-16 md:py-24 px-4">
    <div class="max-w-4xl mx-auto relative z-10 text-center">

        {{-- Badge --}}
        <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 backdrop-blur rounded-full px-4 py-2 text-sm font-semibold mb-6 text-amber-300">
            <span class="live-dot"></span>
            Antrian Real-time · Tidak Perlu Download App
        </div>

        {{-- Headline --}}
        <h1 class="text-4xl md:text-6xl font-black leading-tight mb-5 tracking-tight">
            Cek Antrian Barber<br>
            <span style="background:linear-gradient(135deg,#fbbf24,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Sebelum Berangkat</span>
        </h1>

        <p class="text-slate-300 text-lg md:text-xl max-w-xl mx-auto mb-10 leading-relaxed">
            Lihat keramaian, estimasi waktu tunggu, dan daftar antrian semua cabang — langsung dari sini.
        </p>

        {{-- Search + Filter --}}
        <div class="max-w-xl mx-auto space-y-3">
            <input
                id="search-input"
                type="text"
                placeholder="🔍  Cari nama cabang atau barbershop..."
                class="search-box w-full px-5 py-3.5 text-base"
            >
            <div class="flex items-center justify-center gap-2 flex-wrap">
                <button onclick="setFilter('all')"   class="filter-btn active px-4 py-1.5 text-sm font-semibold" id="filter-all">Semua</button>
                <button onclick="setFilter('open')"  class="filter-btn px-4 py-1.5 text-sm font-semibold" id="filter-open">🟢 Buka</button>
                <button onclick="setFilter('sepi')"  class="filter-btn px-4 py-1.5 text-sm font-semibold" id="filter-sepi">✨ Sepi</button>
                <button onclick="setFilter('sedang')" class="filter-btn px-4 py-1.5 text-sm font-semibold" id="filter-sedang">🟡 Sedang</button>
                <button onclick="setFilter('ramai')" class="filter-btn px-4 py-1.5 text-sm font-semibold" id="filter-ramai">🔴 Ramai</button>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════ CABANG SECTION ══════════════════ --}}
<section class="max-w-6xl mx-auto px-4 py-12">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-black text-slate-900">Daftar Cabang Barber</h2>
            <p class="text-slate-500 text-sm mt-1">
                <span id="visible-count" class="font-bold text-amber-600">{{ $branches->count() }}</span>
                cabang aktif · diperbarui otomatis setiap 10 detik
            </p>
        </div>
        <div class="flex items-center gap-2 text-sm text-slate-500 font-medium bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-sm">
            <span class="live-dot"></span>
            <span>Terakhir: <span id="last-updated-global">--:--:--</span></span>
        </div>
    </div>

    {{-- Branch Grid --}}
    <div id="branch-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($branches as $branch)
        @php
            $qCount  = $branch->active_queue_count ?? 0;
            $status  = $qCount <= 2 ? 'sepi' : ($qCount <= 6 ? 'sedang' : 'ramai');
            $labels  = ['sepi' => '🟢 Sepi', 'sedang' => '🟡 Sedang', 'ramai' => '🔴 Ramai'];
            $isOpen  = $branch->isOpen();
        @endphp
        <div
            class="branch-card {{ !$isOpen ? 'is-closed' : '' }}"
            data-code="{{ $branch->code }}"
            data-name="{{ strtolower($branch->name . ' ' . ($branch->barbershop->name ?? '')) }}"
            data-status="{{ $status }}"
            data-open="{{ $isOpen ? '1' : '0' }}"
        >
            {{-- Card Top: Brand & Status --}}
            <div class="p-5 pb-4">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-2xl shrink-0 shadow-sm overflow-hidden">
                            @if($branch->barbershop->logo ?? null)
                                <img src="{{ asset('storage/' . $branch->barbershop->logo) }}" class="w-full h-full object-cover">
                            @else
                                ✂️
                            @endif
                        </div>
                        <div>
                            <div class="text-xs text-amber-600 font-bold uppercase tracking-wide">{{ $branch->barbershop->name ?? 'Barbershop' }}</div>
                            <h3 class="text-base font-black text-slate-900 leading-tight">{{ $branch->name }}</h3>
                        </div>
                    </div>
                    <div class="shrink-0">
                        @if($isOpen)
                            <span class="text-xs font-bold bg-green-100 text-green-700 border border-green-200 px-2.5 py-1 rounded-full">● Buka</span>
                        @else
                            <span class="text-xs font-bold bg-red-100 text-red-600 border border-red-200 px-2.5 py-1 rounded-full">● Tutup</span>
                        @endif
                    </div>
                </div>

                {{-- Info --}}
                <div class="space-y-1.5 text-xs text-slate-500 mb-4">
                    @if($branch->address)
                    <div class="flex items-start gap-1.5">
                        <svg class="w-3.5 h-3.5 mt-0.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="leading-tight line-clamp-1">{{ $branch->address }}</span>
                    </div>
                    @endif
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ substr($branch->open_time,0,5) }} – {{ substr($branch->close_time,0,5) }}</span>
                        @if($branch->phone)
                        <span class="mx-1 text-slate-300">·</span>
                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>{{ $branch->phone }}</span>
                        @endif
                    </div>
                </div>

                {{-- Queue Stats --}}
                <div class="flex items-center justify-between bg-slate-50 rounded-xl px-4 py-3 border border-slate-100">
                    <div class="text-center">
                        <div class="text-2xl font-black text-slate-800 counter-num branch-queue-count" data-code="{{ $branch->code }}">
                            {{ $qCount }}
                        </div>
                        <div class="text-xs text-slate-500 font-medium mt-0.5">Mengantri</div>
                    </div>
                    <div class="w-px h-8 bg-slate-200"></div>
                    <div class="text-center">
                        <div class="text-2xl font-black text-amber-600 branch-est-wait" data-code="{{ $branch->code }}">
                            {{ $branch->estimated_wait_minutes }}
                        </div>
                        <div class="text-xs text-slate-500 font-medium mt-0.5">Mnt tunggu</div>
                    </div>
                    <div class="w-px h-8 bg-slate-200"></div>
                    <div class="text-center">
                        <span class="text-xs font-bold px-2.5 py-1.5 rounded-full queue-badge-{{ $status }} branch-status-badge" data-code="{{ $branch->code }}">
                            {{ $labels[$status] }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Card Footer: Actions --}}
            <div class="border-t border-slate-100 px-5 py-3.5 flex items-center justify-between bg-slate-50/50">
                <div class="text-xs text-slate-400 font-medium">
                    Kode: <span class="font-bold text-slate-600">{{ strtoupper($branch->code) }}</span>
                </div>
                <a
                    href="{{ route('queue.monitor', $branch->code) }}"
                    class="flex items-center gap-1.5 text-xs font-bold text-amber-600 bg-amber-50 border border-amber-200 px-3.5 py-2 rounded-lg hover:bg-amber-100 transition-colors"
                >
                    👁 Lihat Antrian
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-20">
            <div class="text-5xl mb-4">✂️</div>
            <p class="text-slate-700 font-bold text-lg">Belum ada cabang aktif</p>
            <p class="text-slate-500 text-sm mt-2">Daftarkan barbershop Anda sekarang dan kelola antrian lebih mudah.</p>
            <a href="{{ route('register.barbershop') }}" class="inline-block mt-5 btn-primary px-6 py-3 rounded-xl font-bold">🏪 Daftar Gratis</a>
        </div>
        @endforelse
    </div>

    {{-- No result (filter/search) --}}
    <div id="no-result" class="no-branch-found col-span-3 text-center py-16">
        <div class="text-5xl mb-4">🔍</div>
        <p class="text-slate-700 font-bold text-lg">Tidak ditemukan</p>
        <p class="text-slate-500 text-sm mt-2">Coba kata kunci atau filter lain.</p>
    </div>

</section>

{{-- ══════════════════ HOW IT WORKS ══════════════════ --}}
<section class="bg-white border-t border-slate-100 py-14 px-4">
    <div class="max-w-4xl mx-auto text-center">
        <div class="text-xs text-amber-600 font-bold tracking-wider uppercase mb-2">Untuk Pelanggan</div>
        <h2 class="text-2xl font-black text-slate-900 mb-10">Cara Kerja BarberQ</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach([
                ['👁', 'Cek Antrian', 'Lihat keramaian semua cabang langsung dari halaman ini — tanpa login!', 'amber'],
                ['📱', 'Scan QR di Lokasi', 'Datang ke cabang pilihanmu, scan QR code di meja kasir untuk masuk antrian.', 'orange'],
                ['☕', 'Pantau dari HP', 'Lihat posisi antrianmu secara real-time. Datang tepat saat giliran.', 'green'],
            ] as [$icon, $title, $desc, $color])
            <div class="bg-slate-50 p-7 rounded-3xl border border-slate-100 relative overflow-hidden group hover:border-amber-200 hover:shadow-md transition-all">
                <div class="absolute -right-4 -top-4 w-28 h-28 bg-{{ $color }}-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-60"></div>
                <div class="relative">
                    <div class="text-4xl mb-3">{{ $icon }}</div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">{{ $title }}</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ CTA for Owners ══════════════════ --}}
<section class="bg-slate-900 py-14 px-4 text-white text-center">
    <div class="max-w-2xl mx-auto">
        <div class="text-4xl mb-4">✂️</div>
        <h2 class="text-2xl font-black mb-3">Punya Barbershop?</h2>
        <p class="text-slate-400 text-sm mb-7 leading-relaxed">
            Daftar gratis, pasang QR di meja kasir, dan kelola antrian semua cabang dari satu dashboard.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('register.barbershop') }}"
               class="w-full sm:w-auto px-7 py-3.5 rounded-xl font-bold text-white hover:scale-[1.02] transition-all"
               style="background:linear-gradient(135deg,#f59e0b,#ea580c);">
                🚀 Daftar Gratis Sekarang
            </a>
            <a href="{{ route('login') }}"
               class="w-full sm:w-auto px-7 py-3.5 rounded-xl font-semibold text-slate-300 border border-slate-600 hover:border-slate-400 transition-colors text-sm">
                Sudah punya akun? Login →
            </a>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    // ─── Realtime polling semua cabang ───────────────────────────────────────
    function pollBranches() {
        fetch('/api/branches')
            .then(r => r.json())
            .then(data => {
                document.getElementById('last-updated-global').textContent = data.last_updated;

                data.branches.forEach(b => {
                    // Update queue count
                    document.querySelectorAll(`.branch-queue-count[data-code="${b.code}"]`).forEach(el => {
                        el.textContent = b.queue_count;
                    });
                    // Update est wait
                    document.querySelectorAll(`.branch-est-wait[data-code="${b.code}"]`).forEach(el => {
                        el.textContent = b.estimated_wait;
                    });
                    // Update status badge
                    const badges = document.querySelectorAll(`.branch-status-badge[data-code="${b.code}"]`);
                    const icons  = { sepi: '🟢 Sepi', sedang: '🟡 Sedang', ramai: '🔴 Ramai' };
                    badges.forEach(el => {
                        el.textContent  = icons[b.queue_status] || b.queue_label;
                        el.className = `text-xs font-bold px-2.5 py-1.5 rounded-full queue-badge-${b.queue_status} branch-status-badge`;
                        el.dataset.code = b.code;
                    });
                    // Update card data-status & data-open for filtering
                    const card = document.querySelector(`.branch-card[data-code="${b.code}"]`);
                    if (card) {
                        card.dataset.status = b.queue_status;
                        card.dataset.open   = b.is_open ? '1' : '0';
                    }
                });
            })
            .catch(() => {});
    }

    setInterval(pollBranches, 10000);
    document.getElementById('last-updated-global').textContent = new Date().toLocaleTimeString('id-ID');

    // ─── Search & Filter ─────────────────────────────────────────────────────
    let currentFilter = 'all';

    function setFilter(filter) {
        currentFilter = filter;
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        const btn = document.getElementById('filter-' + filter);
        if (btn) btn.classList.add('active');
        applyFilter();
    }

    document.getElementById('search-input').addEventListener('input', applyFilter);

    function applyFilter() {
        const query = document.getElementById('search-input').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.branch-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name   = (card.dataset.name || '').toLowerCase();
            const status = card.dataset.status;
            const isOpen = card.dataset.open === '1';

            const matchSearch = !query || name.includes(query);
            let matchFilter = true;
            if (currentFilter === 'open')   matchFilter = isOpen;
            if (currentFilter === 'sepi')   matchFilter = status === 'sepi';
            if (currentFilter === 'sedang') matchFilter = status === 'sedang';
            if (currentFilter === 'ramai')  matchFilter = status === 'ramai';

            const visible = matchSearch && matchFilter;
            card.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });

        document.getElementById('visible-count').textContent = visibleCount;
        const noResult = document.getElementById('no-result');
        noResult.style.display = visibleCount === 0 ? 'block' : 'none';
    }
</script>
@endpush
