<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — BarberQ</title>
    <meta name="description" content="Login ke panel administrasi BarberQ">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

        body {
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        /* Subtle background pattern */
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

        .login-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 2.25rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
        }

        .form-input {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.625rem;
            padding: 0.75rem 1rem;
            font-size: 0.925rem;
            color: #0f172a;
            background: #fafafa;
            transition: all 0.2s;
            outline: none;
        }
        .form-input:focus {
            border-color: #f59e0b;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(245,158,11,0.12);
        }
        .form-input::placeholder { color: #94a3b8; }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.375rem;
            letter-spacing: 0.01em;
        }

        .btn-login {
            width: 100%;
            padding: 0.875rem 1.5rem;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, #f59e0b, #ea580c);
            color: #fff;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.01em;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(245,158,11,0.35); }
        .btn-login:active { transform: translateY(0); }
        .btn-login:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

        .error-alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-custom {
            width: 1rem; height: 1rem;
            accent-color: #f59e0b;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="login-card">
        {{-- Logo --}}
        <div class="text-center mb-7">
            <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center text-3xl mb-4 shadow-sm"
                 style="background:linear-gradient(135deg,#fef3c7,#fed7aa);border:1px solid #fbbf24;">
                ✂️
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Barber<span style="background:linear-gradient(135deg,#f59e0b,#ea580c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Q</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1 font-medium">Panel Administrasi Barbershop</p>
        </div>

        {{-- Error --}}
        @if($errors->any())
        <div class="error-alert mb-5">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $errors->first() }}
        </div>
        @endif

        @if(session('error'))
        <div class="error-alert mb-5">{{ session('error') }}</div>
        @endif

        {{-- Form --}}
        <form action="{{ route('login.post') }}" method="POST" id="loginForm" class="space-y-4">
            @csrf

            <div>
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="{{ old('email') }}"
                       class="form-input"
                       placeholder="admin@barbershop.com"
                       required autofocus>
            </div>

            <div>
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password"
                       class="form-input"
                       placeholder="••••••••"
                       required>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="remember" id="remember" class="checkbox-custom">
                <label for="remember" class="text-sm text-slate-500 cursor-pointer select-none">Ingat saya</label>
            </div>

            <button type="submit" id="loginBtn" class="btn-login">
                <span id="loginText">Masuk ke Dashboard</span>
                <span id="loginLoading" class="hidden">Memverifikasi...</span>
            </button>
        </form>

        {{-- Divider --}}
        <div class="flex items-center gap-3 my-5">
            <div class="flex-1 h-px bg-slate-100"></div>
            <span class="text-xs text-slate-400 font-medium">atau</span>
            <div class="flex-1 h-px bg-slate-100"></div>
        </div>

        {{-- Register Link --}}
        <a href="{{ route('register.barbershop') }}"
           class="flex items-center justify-center gap-2 w-full py-3 rounded-xl border-2 border-amber-200 text-amber-700 text-sm font-bold hover:bg-amber-50 transition-colors">
            🏪 Daftarkan Barbershop Anda
        </a>

        {{-- Back --}}
        <div class="text-center mt-5">
            <a href="{{ route('home') }}" class="text-xs text-slate-400 hover:text-slate-600 transition-colors font-medium">
                ← Kembali ke halaman utama
            </a>
        </div>
    </div>

    <script>
        // Reset tombol jika halaman diload dari bfcache (bfcache = back-forward cache browser)
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                // Halaman dikembalikan dari bfcache, reset state tombol
                document.getElementById('loginText').classList.remove('hidden');
                document.getElementById('loginLoading').classList.add('hidden');
                document.getElementById('loginBtn').disabled = false;
            }
        });

        document.getElementById('loginForm').addEventListener('submit', function () {
            document.getElementById('loginText').classList.add('hidden');
            document.getElementById('loginLoading').classList.remove('hidden');
            document.getElementById('loginBtn').disabled = true;
        });
    </script>
</body>
</html>
