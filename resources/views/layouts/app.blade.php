<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BarberQ — Sistem Antrian Barbershop Digital. Lihat antrian real-time, masuk & keluar antrian via QR Code.">
    <title>@yield('title', 'BarberQ — Antrian Barbershop Digital')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        amber: {
                            400: '#fbbf24', 500: '#f59e0b', 600: '#d97706',
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.4s ease-out',
                    },
                    keyframes: {
                        fadeIn:  { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        slideUp: { '0%': { opacity: '0', transform: 'translateY(20px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                    }
                }
            }
        }
    </script>

    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f8fafc; color: #0f172a; }
        .glass { background: rgba(255,255,255,0.85); backdrop-filter: blur(16px); border-bottom: 1px solid #e2e8f0; }
        .glass-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05); transition: all 0.3s ease; }
        .gradient-text { background: linear-gradient(135deg, #f59e0b, #ea580c); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .glow-amber { box-shadow: 0 10px 40px -10px rgba(245, 158, 11, 0.3); }
        .status-sepi   { background: #dcfce3; color: #16a34a; border: 1px solid #bbf7d0; }
        .status-sedang { background: #fef9c3; color: #ca8a04; border: 1px solid #fef08a; }
        .status-ramai  { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .btn-primary { background: linear-gradient(135deg, #f59e0b, #ea580c); color: #ffffff; font-weight: 700; transition: all 0.2s; box-shadow: 0 4px 14px 0 rgba(234, 88, 12, 0.39); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(234, 88, 12, 0.4); }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .nav-link { color: #475569; transition: color 0.2s; font-weight: 500; }
        .nav-link:hover { color: #f59e0b; }
        @keyframes countUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .count-animate { animation: countUp 0.4s ease-out; }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen text-slate-800 antialiased">

    <!-- Navbar -->
    <nav class="glass sticky top-0 z-50 px-4 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-amber-500 flex items-center justify-center font-black text-white text-lg shadow-lg group-hover:scale-105 transition-transform">
                    ✂
                </div>
                <span class="font-bold text-xl tracking-tight text-slate-800">
                    Barber<span class="text-amber-500">Q</span>
                </span>
            </a>
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="nav-link hidden sm:block">Beranda</a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="nav-link hidden sm:block">Dashboard</a>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-slate-500 hover:text-red-500 transition-colors font-medium">Logout</button>
                    </form>
                @else
                    <a href="{{ route('register.barbershop') }}" class="hidden sm:block text-sm font-semibold text-amber-600 hover:text-amber-700 transition-colors border border-amber-200 bg-amber-50 px-3 py-1.5 rounded-lg">
                        🏪 Daftar
                    </a>
                    <a href="{{ route('login') }}" class="btn-primary px-4 py-2 rounded-lg text-sm">
                        Admin Login
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 pt-4">
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2 animate-fade-in shadow-sm">
                <svg class="w-4 h-4 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 pt-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2 animate-fade-in shadow-sm">
                <svg class="w-4 h-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-16 border-t border-slate-200 py-8 text-center text-slate-500 text-sm bg-white">
        <p>© {{ date('Y') }} <span class="text-amber-500 font-bold">BarberQ</span> — Sistem Antrian Barbershop Digital</p>
    </footer>

    @stack('scripts')
</body>
</html>
