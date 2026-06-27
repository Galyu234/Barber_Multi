<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Barbershop — BarberQ</title>
    <meta name="description" content="Daftarkan barbershop Anda ke platform BarberQ dan kelola antrian secara digital.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: #f8fafc;
            min-height: 100vh;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 20%, rgba(251,191,36,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 80%, rgba(59,130,246,0.06) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }
        .glass-card { position: relative; z-index: 1; background: #fff; border: 1px solid #e2e8f0; border-radius: 1.25rem; box-shadow: 0 4px 24px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04); }
        .form-input { background: #fafafa; border: 1.5px solid #e2e8f0; color: #0f172a; border-radius: 0.625rem; padding: 0.75rem 1rem; font-size: 0.925rem; width: 100%; transition: all 0.2s; outline: none; }
        .form-input:focus { border-color: #f59e0b; background: #fff; box-shadow: 0 0 0 3px rgba(245,158,11,0.12); }
        .form-input::placeholder { color: #94a3b8; }
        .form-label { display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 0.375rem; letter-spacing: 0.01em; }
        .btn-register { background: linear-gradient(135deg, #f59e0b, #ea580c); color: #fff; font-weight: 700; padding: 0.9rem 1.5rem; border-radius: 0.75rem; width: 100%; font-size: 0.95rem; transition: all 0.2s; border: none; cursor: pointer; letter-spacing: 0.01em; position: relative; z-index: 1; }
        .btn-register:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(245,158,11,0.35); }
        .btn-register:active { transform: translateY(0); }
        .step-badge { width: 1.75rem; height: 1.75rem; border-radius: 50%; background: #fef3c7; border: 1px solid #fde68a; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; flex-shrink: 0; }
        .section-divider { border-color: #f1f5f9; }
        .error-msg { color: #dc2626; font-size: 0.8rem; margin-top: 0.25rem; }
    </style>
</head>
<body class="py-8 px-4">

    <!-- Navbar mini -->
    <div class="max-w-2xl mx-auto mb-8 relative z-10">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group w-fit">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-lg shadow-sm"
                 style="background:linear-gradient(135deg,#fef3c7,#fed7aa);border:1px solid #fbbf24;">✂</div>
            <span class="font-bold text-xl tracking-tight text-slate-900">Barber<span class="text-amber-500">Q</span></span>
        </a>
    </div>

    <div class="max-w-2xl mx-auto">

        <!-- Hero Header -->
        <div class="text-center mb-8 relative z-10">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-100 border border-amber-200 text-amber-700 text-xs font-bold mb-4">
                🚀 Platform SaaS Barbershop
            </div>
            <h1 class="text-3xl font-black text-slate-900 mb-2">Daftarkan Barbershop Anda</h1>
            <p class="text-slate-500 text-sm font-medium">Buat akun dalam 1 menit. Gratis. Langsung bisa pakai.</p>
        </div>

        <!-- How it works mini -->
        <div class="grid grid-cols-3 gap-3 mb-8">
            @foreach([['📝','Isi form','Data barbershop & cabang pertama'], ['⚡','Auto setup','Sistem buat akun & QR otomatis'], ['📱','Langsung pakai','Login & pasang QR di toko']] as [$icon, $title, $desc])
            <div class="glass-card p-3 text-center">
                <div class="text-2xl mb-1">{{ $icon }}</div>
                <div class="text-xs font-bold text-slate-800">{{ $title }}</div>
                <div class="text-xs text-slate-500 mt-0.5 font-medium">{{ $desc }}</div>
            </div>
            @endforeach
        </div>

        <!-- Main Form -->
        <div class="glass-card p-7">
            @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm mb-6 flex items-start gap-2">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm mb-6">
                {{ session('error') }}
            </div>
            @endif

            <form action="{{ route('register.barbershop.post') }}" method="POST" id="registerForm">
                @csrf

                <!-- Section 1: Barbershop -->
                <div class="flex items-center gap-3 mb-5">
                    <div class="step-badge">1</div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Informasi Barbershop</h2>
                        <p class="text-xs text-slate-500">Nama bisnis dan pemilik</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="sm:col-span-2">
                        <label class="form-label" for="barbershop_name">Nama Barbershop *</label>
                        <input type="text" id="barbershop_name" name="barbershop_name"
                               value="{{ old('barbershop_name') }}"
                               class="form-input @error('barbershop_name') border-red-500/50 @enderror"
                               placeholder="cth: Rapih Barbershop" required autofocus>
                        @error('barbershop_name')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label" for="owner_name">Nama Pemilik *</label>
                        <input type="text" id="owner_name" name="owner_name"
                               value="{{ old('owner_name') }}"
                               class="form-input @error('owner_name') border-red-500/50 @enderror"
                               placeholder="Nama lengkap Anda" required>
                        @error('owner_name')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label" for="phone">Nomor Telepon</label>
                        <input type="text" id="phone" name="phone"
                               value="{{ old('phone') }}"
                               class="form-input @error('phone') border-red-500/50 @enderror"
                               placeholder="08xx-xxxx-xxxx">
                        @error('phone')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="form-label" for="address">Alamat Cabang Pertama</label>
                        <input type="text" id="address" name="address"
                               value="{{ old('address') }}"
                               class="form-input @error('address') border-red-500/50 @enderror"
                               placeholder="Jl. Merdeka No. 1, Jakarta Pusat">
                        @error('address')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                </div>

                <hr class="section-divider mb-6">

                <!-- Section 2: Cabang Pertama -->
                <div class="flex items-center gap-3 mb-5">
                    <div class="step-badge">2</div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Cabang Pertama</h2>
                        <p class="text-xs text-slate-500">Nama cabang yang akan langsung aktif</p>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="form-label" for="branch_name">Nama Cabang *</label>
                    <input type="text" id="branch_name" name="branch_name"
                           value="{{ old('branch_name') }}"
                           class="form-input @error('branch_name') border-red-500/50 @enderror"
                           placeholder="cth: Rapih - Pusat" required>
                    <p class="text-xs text-slate-500 mt-1.5">💡 Kode cabang unik akan dibuat otomatis (cth: RA001)</p>
                    @error('branch_name')<p class="error-msg">{{ $message }}</p>@enderror
                </div>

                <hr class="section-divider mb-6">

                <!-- Section 3: Akun -->
                <div class="flex items-center gap-3 mb-5">
                    <div class="step-badge">3</div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Akun Admin</h2>
                        <p class="text-xs text-slate-500">Email & password untuk login dashboard</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="sm:col-span-2">
                        <label class="form-label" for="email">Email *</label>
                        <input type="email" id="email" name="email"
                               value="{{ old('email') }}"
                               class="form-input @error('email') border-red-500/50 @enderror"
                               placeholder="admin@barbershop.com" required>
                        @error('email')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label" for="password">Password *</label>
                        <input type="password" id="password" name="password"
                               class="form-input @error('password') border-red-500/50 @enderror"
                               placeholder="Min. 8 karakter" required minlength="8">
                        @error('password')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label" for="password_confirmation">Konfirmasi Password *</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-input"
                               placeholder="Ulangi password" required>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" id="submitBtn" class="btn-register">
                    <span id="submitText">🚀 Daftarkan Barbershop Saya</span>
                    <span id="submitLoading" class="hidden">⏳ Membuat akun...</span>
                </button>

                <p class="text-xs text-slate-500 text-center mt-4">
                    Dengan mendaftar, Anda setuju dengan ketentuan penggunaan BarberQ.
                </p>
            </form>
        </div>

        <!-- Login link -->
        <div class="text-center mt-6 relative z-10">
            <p class="text-slate-500 text-sm font-medium">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-amber-600 hover:text-amber-700 font-bold transition-colors">
                    Login di sini →
                </a>
            </p>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('home') }}" class="text-xs text-slate-600 hover:text-slate-400 transition-colors">
                ← Kembali ke halaman utama
            </a>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function() {
            document.getElementById('submitText').classList.add('hidden');
            document.getElementById('submitLoading').classList.remove('hidden');
            document.getElementById('submitBtn').disabled = true;
        });
    </script>
</body>
</html>
