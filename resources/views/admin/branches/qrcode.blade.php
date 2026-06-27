@extends('layouts.admin')

@section('title', 'QR Code — ' . $branch->name)
@section('page-title', 'QR Code Cabang')

@section('content')
<div class="max-w-xl mx-auto">
    {{-- Back Button --}}
    @if(auth()->user()->isSuperAdmin())
    <a href="{{ route('admin.branches.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm mb-6 transition-colors font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke daftar cabang
    </a>
    @else
    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm mb-6 transition-colors font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Dashboard
    </a>
    @endif

    {{-- Branch Info --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5 mb-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">
                {{ $branch->code }}
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">{{ $branch->name }}</h2>
                <p class="text-sm text-slate-500">{{ $branch->barbershop->name }}</p>
            </div>
        </div>
    </div>

    {{-- QR Code Card --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center shadow-sm mb-6">
        <div class="w-12 h-12 mx-auto rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-2xl mb-4">🏪</div>
        <h3 class="font-bold text-slate-900 text-lg mb-1">QR Code Cabang</h3>
        <p class="text-sm text-slate-500 mb-6">Scan QR ini untuk masuk ke halaman antrian cabang.</p>

        {{-- SVG QR Display --}}
        <div id="qr-svg-wrapper" class="bg-white border border-slate-100 rounded-2xl p-4 inline-block mb-5 shadow-md">
            {!! $branchSvg !!}
        </div>

        {{-- URL Display --}}
        <div class="text-sm text-slate-600 mb-6 break-all bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-mono text-xs">
            {{ $branchUrl }}
        </div>

        {{-- Download Buttons --}}
        <div class="grid grid-cols-2 gap-3">
            {{-- PNG: JS canvas-based download (no imagick required) --}}
            <button id="btn-download-png" onclick="downloadQrAsPng()"
               class="flex items-center justify-center gap-2 bg-blue-600 text-white hover:bg-blue-700 py-3 rounded-xl font-semibold shadow-sm transition-colors text-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download PNG
            </button>
            <a href="{{ route('admin.branches.qrcode.download.svg', $branch) }}"
               class="flex items-center justify-center gap-2 bg-slate-700 text-white hover:bg-slate-800 py-3 rounded-xl font-semibold shadow-sm transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download SVG
            </a>
        </div>
    </div>

    {{-- Open in Browser --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-4 mb-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-800">Halaman Publik Cabang</p>
                <p class="text-xs text-slate-500 mt-0.5">Buka halaman antrian yang dilihat pelanggan</p>
            </div>
            <a href="{{ route('branch.detail', $branch->code) }}" target="_blank"
               class="btn-secondary flex items-center gap-1.5 text-xs">
                Buka
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>

    {{-- Instructions --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h4 class="font-bold text-slate-900 mb-4">📋 Cara Penggunaan</h4>
        <div class="space-y-3 text-sm text-slate-600">
            <div class="flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 font-bold text-xs mt-0.5">1</span>
                <span>Download QR code dalam format PNG (untuk print) atau SVG (untuk edit).</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 font-bold text-xs mt-0.5">2</span>
                <span>Print dan pasang QR Code di depan toko atau area kasir barbershop Anda.</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 font-bold text-xs mt-0.5">3</span>
                <span>Pelanggan scan QR → masuk halaman antrian → klik <strong>"Masuk Antrian"</strong> → dapat nomor antrian.</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 font-bold text-xs mt-0.5">4</span>
                <span>Admin dapat scan QR tiket pelanggan di menu <strong>"Scan QR Pelanggan"</strong> untuk menyelesaikan antrian.</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
/**
 * Download QR as PNG using Canvas — no server-side imagick required.
 * Renders the displayed SVG into a canvas and exports as PNG.
 */
function downloadQrAsPng() {
    const btn = document.getElementById('btn-download-png');
    btn.textContent = 'Memproses...';
    btn.disabled = true;

    const svgEl = document.querySelector('#qr-svg-wrapper svg');
    if (!svgEl) {
        alert('QR Code tidak ditemukan.');
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Download PNG';
        btn.disabled = false;
        return;
    }

    const size = 800;
    const svgClone = svgEl.cloneNode(true);
    svgClone.setAttribute('width', size);
    svgClone.setAttribute('height', size);
    svgClone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');

    const svgData = new XMLSerializer().serializeToString(svgClone);
    const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(svgBlob);

    const img = new Image();
    img.onload = function () {
        const canvas = document.createElement('canvas');
        canvas.width = size;
        canvas.height = size;
        const ctx = canvas.getContext('2d');

        // White background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, size, size);
        ctx.drawImage(img, 0, 0, size, size);

        URL.revokeObjectURL(url);

        canvas.toBlob(function (blob) {
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'QR_{{ $branch->code }}_branch.png';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(a.href);

            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Download PNG';
            btn.disabled = false;
        }, 'image/png');
    };
    img.onerror = function () {
        alert('Gagal menghasilkan PNG. Gunakan Download SVG sebagai alternatif.');
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Download PNG';
        btn.disabled = false;
        URL.revokeObjectURL(url);
    };
    img.src = url;
}
</script>
@endpush
