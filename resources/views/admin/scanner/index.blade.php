@extends('layouts.admin')

@section('title', 'Scan QR Pelanggan')
@section('page-title', 'Scan QR Pelanggan')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Branch Selector (super admin or tenant admin with multiple branches) --}}
    @if(auth()->user()->isSuperAdmin() || (auth()->user()->isTenantAdmin() && $branches->count() > 1))
    <div class="admin-card p-4 mb-5">
        <form method="GET" class="flex items-end gap-3">
            <div class="flex-1">
                <label class="form-label">Pilih Cabang</label>
                <select name="branch_id" class="form-input" onchange="this.form.submit()">
                    <option value="">— Pilih Cabang —</option>
                    @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                        {{ $b->name }} ({{ $b->code }})
                    </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    @endif

    {{-- Scanner Card --}}
    <div class="admin-card p-6 mb-5">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-blue-500/15 border border-blue-500/20 flex items-center justify-center text-xl">📷</div>
            <div>
                <h2 class="font-bold text-slate-800 text-base">Kamera QR Scanner</h2>
                <p class="text-xs text-slate-500 mt-0.5">Arahkan kamera ke QR tiket pelanggan</p>
            </div>
        </div>

        {{-- Lifecycle Info --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 mb-5">
            <p class="text-xs font-semibold text-blue-600 mb-2 uppercase tracking-wider">📋 Alur Kerja Scanner</p>
            <div class="flex items-center gap-2 text-xs flex-wrap">
                <span class="badge badge-yellow">⏳ Menunggu</span>
                <span class="text-slate-400">→ Scan QR →</span>
                <span class="badge badge-blue">✂️ Sedang Dicukur</span>
                <span class="text-slate-400">→ Scan QR →</span>
                <span class="badge badge-green">✅ Selesai</span>
            </div>
            <p class="text-xs text-blue-500 mt-2">⚠️ Aksi hanya bisa dijalankan melalui <strong>scan QR kamera</strong>. Input manual hanya untuk cek status.</p>
        </div>

        {{-- Camera View --}}
        <div id="scanner-container" class="relative bg-slate-900 rounded-2xl overflow-hidden mb-4" style="min-height: 280px;">
            <div id="reader" class="w-full rounded-2xl overflow-hidden"></div>
            <div id="scanner-placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-slate-500">
                <div class="text-5xl mb-3 opacity-40">📷</div>
                <p class="text-sm font-medium opacity-60">Klik tombol untuk mulai scan</p>
            </div>
        </div>

        {{-- Controls --}}
        <div class="flex gap-3 mb-5">
            <button id="btn-start" onclick="startScanner()"
                class="flex-1 btn-primary py-3 rounded-xl flex items-center justify-center gap-2 text-sm font-bold">
                <span>📷</span> Mulai Kamera
            </button>
            <button id="btn-stop" onclick="stopScanner()"
                class="flex-1 btn-secondary py-3 rounded-xl flex items-center justify-center gap-2 text-sm font-bold hidden">
                <span>⏹</span> Stop Kamera
            </button>
        </div>

        {{-- Manual Input --}}
        <div class="border-t border-slate-200 pt-4">
            <label class="form-label mb-2">Atau Input Token Manual</label>
            <div class="flex gap-2">
                <input type="text" id="manual-token" placeholder="Paste token QR di sini..."
                       class="form-input flex-1 text-sm" onkeydown="if(event.key==='Enter') lookupToken()">
                <button onclick="lookupToken()" class="btn-primary px-4 rounded-lg text-sm font-semibold whitespace-nowrap">
                    Cari
                </button>
            </div>
        </div>
    </div>

    {{-- Result Card --}}
    <div id="result-card" class="admin-card p-6 hidden">
        <div class="flex items-center gap-3 mb-5">
            <div id="result-icon" class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl bg-blue-100">🎫</div>
            <div>
                <h3 id="result-queue-number" class="text-2xl font-black text-slate-800"></h3>
                <p id="result-branch" class="text-sm text-slate-500"></p>
            </div>
            <span id="result-status-badge" class="ml-auto badge"></span>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-5">
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                <div class="text-xs text-slate-400 mb-1">Posisi Antrian</div>
                <div id="result-position" class="text-xl font-black text-slate-800">—</div>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                <div class="text-xs text-slate-400 mb-1">Waktu Masuk</div>
                <div id="result-joined" class="text-xl font-black text-slate-800">—</div>
            </div>
        </div>

        {{-- Dynamic Action Button (hanya tampil setelah scan QR kamera) --}}
        <div id="action-buttons" class="hidden space-y-2">
            {{-- Scan 1: waiting → in_progress --}}
            <button id="btn-start-cut"
                onclick="advanceQueue()"
                class="w-full py-4 rounded-xl font-bold text-white text-base transition-all hover:scale-[1.02] active:scale-[0.99] hidden"
                style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                ✂️ Mulai Cukur
            </button>

            {{-- Scan 2: in_progress → completed --}}
            <button id="btn-finish-cut"
                onclick="advanceQueue()"
                class="w-full py-4 rounded-xl font-bold text-white text-base transition-all hover:scale-[1.02] active:scale-[0.99] hidden"
                style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                ✅ Selesaikan Antrian
            </button>
        </div>

        {{-- Panduan scan (muncul jika lookup dari manual, bukan dari scan kamera) --}}
        <div id="scan-required-notice" class="hidden bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm">
            <div class="flex items-center gap-2 font-semibold text-amber-700">
                <span>📷</span> Scan QR pelanggan untuk eksekusi aksi
            </div>
            <p class="text-amber-600 text-xs mt-1">Input manual hanya untuk cek status. Arahkan kamera ke QR tiket pelanggan untuk melanjutkan.</p>
        </div>

        {{-- Already Done --}}
        <div id="done-notice" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-semibold flex items-center gap-2">
            <span>✅</span> Antrian sudah selesai.
        </div>

        <div id="result-message" class="hidden mt-3"></div>

        <button onclick="resetScanner()" class="w-full mt-3 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition-colors">
            🔄 Scan Berikutnya
        </button>
    </div>

    {{-- Error Card --}}
    <div id="error-card" class="admin-card p-5 hidden border border-red-200 bg-red-50">
        <div class="flex items-center gap-3">
            <span class="text-2xl">❌</span>
            <div>
                <p class="font-bold text-red-700 text-sm">QR Tidak Ditemukan</p>
                <p id="error-msg" class="text-xs text-red-600 mt-0.5"></p>
            </div>
        </div>
        <button onclick="resetScanner()" class="mt-3 w-full py-2 rounded-xl border border-red-200 text-red-600 text-sm font-semibold hover:bg-red-100 transition-colors">
            Coba Lagi
        </button>
    </div>

</div>
@endsection

@push('scripts')
{{-- html5-qrcode library --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    const BRANCH_ID  = {{ $selectedBranchId ?? 'null' }};
    const CSRF_TOKEN = '{{ csrf_token() }}';

    let html5QrCode = null;
    let currentQueueId = null;
    let scannerRunning = false;
    let lastLookupFromScan = false; // flag: apakah hasil lookup berasal dari scan QR kamera

    /* ── SCANNER ─────────────────────────────────── */
    function startScanner() {
        if (scannerRunning) return;

        document.getElementById('scanner-placeholder').style.display = 'none';
        document.getElementById('btn-start').classList.add('hidden');
        document.getElementById('btn-stop').classList.remove('hidden');

        html5QrCode = new Html5Qrcode("reader");

        const config = {
            fps: 15,
            // Hilangkan qrbox constraint agar sistem memindai seluruh frame kamera, 
            // sangat membantu untuk webcam laptop yang blur atau off-center
            // qrbox: { width: 220, height: 220 },
            aspectRatio: 1.0,
            disableFlip: false, // Penting untuk webcam laptop yang sering mirrored
        };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            (decodedText) => {
                stopScanner();
                lastLookupFromScan = true; // berasal dari scan kamera
                processQrResult(decodedText);
            },
            () => {}
        ).then(() => {
            scannerRunning = true;
        }).catch(err => {
            // Fallback ke kamera depan jika belakang tidak tersedia
            html5QrCode.start(
                { facingMode: "user" },
                config,
                (decodedText) => {
                    stopScanner();
                    lastLookupFromScan = true; // berasal dari scan kamera (front)
                    processQrResult(decodedText);
                },
                () => {}
            ).then(() => {
                scannerRunning = true;
            }).catch(() => {
                showError('Kamera tidak dapat diakses. Pastikan izin kamera diberikan.');
                resetCameraUI();
            });
        });
    }

    function stopScanner() {
        if (html5QrCode && scannerRunning) {
            html5QrCode.stop().then(() => {
                scannerRunning = false;
                resetCameraUI();
            }).catch(() => {
                scannerRunning = false;
                resetCameraUI();
            });
        } else {
            resetCameraUI();
        }
    }

    function resetCameraUI() {
        document.getElementById('btn-start').classList.remove('hidden');
        document.getElementById('btn-stop').classList.add('hidden');
        document.getElementById('scanner-placeholder').style.display = 'flex';
    }

    /* ── PROCESS QR ──────────────────────────────── */
    function processQrResult(text) {
        let token = text;
        try {
            const url  = new URL(text);
            const match = url.pathname.match(/\/queue\/status\/[^\/]+\/([^\/]+)/);
            if (match && match[1]) {
                token = match[1];
            } else {
                // Fallback jika format pathname beda tapi masih URL
                const parts = url.pathname.split('/').filter(p => p.length > 0);
                token = parts[parts.length - 1] || text;
            }
        } catch (e) {
            token = text;
        }

        document.getElementById('manual-token').value = token;
        lookupToken(token);
    }

    /* ── LOOKUP ──────────────────────────────────── */
    function lookupToken(tokenOverride) {
        const token = tokenOverride || document.getElementById('manual-token').value.trim();
        if (!token) return;

        // Jika dipanggil dari tombol manual (bukan dari processQrResult), reset flag scan
        if (tokenOverride === undefined) {
            lastLookupFromScan = false;
        }

        hideAll();

        fetch('{{ route("admin.scanner.lookup") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({ token }),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.found) {
                showError(data.message || 'QR tidak valid atau tidak ditemukan.');
                return;
            }
            showResult(data);
        })
        .catch(() => showError('Terjadi kesalahan koneksi. Coba lagi.'));
    }

    /* ── SHOW RESULT ─────────────────────────────── */
    function showResult(data) {
        currentQueueId = data.queue_id;

        const statusColors = {
            waiting:     'badge-yellow',
            in_progress: 'badge-blue',
            completed:   'badge-green',
            serving:     'badge-blue',
            done:        'badge-green',
            timeout:     'badge-red',
            cancelled:   'badge-gray',
        };

        const iconBg = {
            waiting:     'bg-yellow-100',
            in_progress: 'bg-blue-100',
            completed:   'bg-green-100',
            serving:     'bg-blue-100',
            done:        'bg-green-100',
        };

        document.getElementById('result-queue-number').textContent = 'Nomor ' + data.queue_number;
        document.getElementById('result-branch').textContent       = '📍 ' + data.branch_name;
        document.getElementById('result-position').textContent     = data.position > 0 ? '#' + data.position : '—';
        document.getElementById('result-joined').textContent       = data.joined_at;

        const badge = document.getElementById('result-status-badge');
        badge.textContent = data.status_label;
        badge.className   = 'badge ' + (statusColors[data.status] || 'badge-gray');

        const icon = document.getElementById('result-icon');
        icon.className = `w-12 h-12 rounded-2xl flex items-center justify-center text-2xl ${iconBg[data.status] || 'bg-slate-100'}`;

        // Tampilkan tombol HANYA jika lookup berasal dari scan QR kamera
        const actionBtns        = document.getElementById('action-buttons');
        const btnStart          = document.getElementById('btn-start-cut');
        const btnFinish         = document.getElementById('btn-finish-cut');
        const doneNotice        = document.getElementById('done-notice');
        const scanRequiredNotice = document.getElementById('scan-required-notice');

        btnStart.classList.add('hidden');
        btnFinish.classList.add('hidden');
        doneNotice.classList.add('hidden');
        actionBtns.classList.add('hidden');
        scanRequiredNotice.classList.add('hidden');

        if (data.action === 'done') {
            // Antrian sudah selesai — tampilkan notif selesai (tidak perlu scan)
            doneNotice.classList.remove('hidden');
        } else if (lastLookupFromScan) {
            // Berasal dari scan kamera → tampilkan tombol aksi
            if (data.action === 'start') {
                actionBtns.classList.remove('hidden');
                btnStart.classList.remove('hidden');
            } else if (data.action === 'finish') {
                actionBtns.classList.remove('hidden');
                btnFinish.classList.remove('hidden');
            }
        } else {
            // Berasal dari input manual → hanya tampilkan panduan scan
            if (data.action === 'start' || data.action === 'finish') {
                scanRequiredNotice.classList.remove('hidden');
            }
        }

        document.getElementById('result-message').classList.add('hidden');
        document.getElementById('result-card').classList.remove('hidden');
    }

    /* ── ADVANCE QUEUE (two-step) ────────────────── */
    function advanceQueue() {
        if (!currentQueueId) return;

        // Disable kedua tombol
        const btnStart  = document.getElementById('btn-start-cut');
        const btnFinish = document.getElementById('btn-finish-cut');
        const activeBtn = !btnStart.classList.contains('hidden') ? btnStart : btnFinish;

        activeBtn.disabled    = true;
        activeBtn.textContent = '⏳ Menyimpan...';

        fetch('{{ route("admin.scanner.complete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({ queue_id: currentQueueId }),
        })
        .then(r => r.json())
        .then(data => {
            const msgEl = document.getElementById('result-message');
            msgEl.classList.remove('hidden');

            if (data.success) {
                const color = data.new_status === 'completed' ? 'green' : 'blue';
                msgEl.innerHTML = `<div class="bg-${color}-50 border border-${color}-200 text-${color}-700 px-4 py-3 rounded-xl text-sm font-semibold flex items-center gap-2"><span>${data.new_status === 'completed' ? '✅' : '✂️'}</span>${data.message}</div>`;

                // Re-fetch untuk update tombol sesuai status terbaru
                lookupToken(document.getElementById('manual-token').value);
            } else {
                msgEl.innerHTML = `<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-semibold">${data.message}</div>`;
                activeBtn.disabled    = false;
                activeBtn.textContent = activeBtn.id === 'btn-start-cut' ? '✂️ Mulai Cukur' : '✅ Selesaikan Antrian';
            }
        })
        .catch(() => {
            activeBtn.disabled    = false;
            activeBtn.textContent = activeBtn.id === 'btn-start-cut' ? '✂️ Mulai Cukur' : '✅ Selesaikan Antrian';
        });
    }

    /* ── HELPERS ─────────────────────────────────── */
    function showError(msg) {
        document.getElementById('error-msg').textContent = msg;
        document.getElementById('error-card').classList.remove('hidden');
        document.getElementById('result-card').classList.add('hidden');
    }

    function hideAll() {
        document.getElementById('result-card').classList.add('hidden');
        document.getElementById('error-card').classList.add('hidden');
    }

    function resetScanner() {
        hideAll();
        currentQueueId = null;
        lastLookupFromScan = false;
        document.getElementById('manual-token').value = '';
        document.getElementById('action-buttons').classList.add('hidden');
        document.getElementById('scan-required-notice').classList.add('hidden');
        document.getElementById('result-message').classList.add('hidden');
        document.getElementById('btn-start-cut').disabled    = false;
        document.getElementById('btn-start-cut').textContent = '✂️ Mulai Cukur';
        document.getElementById('btn-finish-cut').disabled    = false;
        document.getElementById('btn-finish-cut').textContent = '✅ Selesaikan Antrian';
    }
</script>
@endpush
