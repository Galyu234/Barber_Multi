<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — BarberQ Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] } } }
        }
    </script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f8fafc; }
        .sidebar { background: #ffffff; border-right: 1px solid #e2e8f0; }
        .sidebar-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.625rem 1rem; border-radius: 0.625rem; color: #64748b; font-size: 0.875rem; font-weight: 500; transition: all 0.15s; text-decoration: none; }
        .sidebar-link:hover { background: #f1f5f9; color: #0f172a; }
        .sidebar-link.active { background: #eff6ff; color: #2563eb; font-weight: 600; }
        .sidebar-link .icon { width: 1.25rem; height: 1.25rem; flex-shrink: 0; }
        .admin-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); }
        .stat-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.875rem; padding: 1.25rem; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); }
        .glass { background: rgba(255,255,255,0.97); backdrop-filter: blur(12px); border-bottom: 1px solid #e2e8f0; }
        .btn-primary { background: #2563eb; color: #ffffff; font-weight: 600; transition: all 0.2s; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-danger  { background: #fee2e2; color: #ef4444; font-weight: 600; transition: all 0.2s; padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.8125rem; }
        .btn-danger:hover  { background: #fca5a5; }
        .btn-secondary { background: #ffffff; color: #475569; border: 1px solid #cbd5e1; font-weight: 500; transition: all 0.2s; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; }
        .btn-secondary:hover { background: #f8fafc; color: #0f172a; }
        .form-input { background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; border-radius: 0.5rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; width: 100%; transition: border 0.15s; }
        .form-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 1px #3b82f6; }
        .form-label { display: block; font-size: 0.8125rem; font-weight: 600; color: #475569; margin-bottom: 0.375rem; }
        .table-row { border-bottom: 1px solid #e2e8f0; }
        .table-row:hover { background: #f8fafc; }
        .badge { display: inline-flex; align-items: center; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .badge-yellow { background: #fef9c3; color: #ca8a04; }
        .badge-blue   { background: #dbeafe; color: #2563eb; }
        .badge-green  { background: #dcfce3;  color: #16a34a; }
        .badge-red    { background: #fee2e2;  color: #dc2626; }
        .badge-gray   { background: #f1f5f9; color: #475569; }

        /* ── Sidebar: mobile = fixed slide-in, desktop = static in document flow ── */
        #sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: 16rem;
            z-index: 50;
            transform: translateX(-100%);
            transition: transform 0.25s ease;
            display: flex;
            flex-direction: column;
        }
        #sidebar.open { transform: translateX(0); }

        @media (min-width: 1024px) {
            #sidebar {
                position: static;
                width: 16rem;
                flex-shrink: 0;
                transform: none !important;
                transition: none;
            }
            #sidebar-toggle { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen text-slate-800 antialiased">

    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black/60 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>

    <!-- Page Wrapper: flex row on desktop -->
    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar">
            <!-- Logo -->
            <div class="p-5 border-b border-slate-200 shrink-0">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center font-black text-white text-base shadow-sm">✂</div>
                    <span class="font-bold text-lg text-slate-900">Barber<span class="text-blue-600">Q</span></span>
                </a>
                <p class="text-xs text-slate-500 mt-1">Admin Panel</p>
            </div>

            <!-- User Info -->
            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 shrink-0">
                <p class="text-xs text-slate-500">Login sebagai</p>
                <p class="text-sm font-semibold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                <span class="text-xs px-2 py-0.5 rounded-full {{ auth()->user()->isSuperAdmin() ? 'bg-blue-100 text-blue-700' : (auth()->user()->isTenantAdmin() ? 'bg-emerald-100 text-emerald-700' : 'bg-purple-100 text-purple-700') }} font-medium">
                    {{ auth()->user()->isSuperAdmin() ? 'Super Admin' : (auth()->user()->isTenantAdmin() ? 'Pemilik Barbershop' : 'Admin Cabang') }}
                </span>
                @if(auth()->user()->isTenantAdmin() && auth()->user()->barbershop)
                <p class="text-xs text-slate-500 mt-1 truncate">🏪 {{ auth()->user()->barbershop->name }}</p>
                @elseif(!auth()->user()->isSuperAdmin() && !auth()->user()->isTenantAdmin() && auth()->user()->branch)
                <p class="text-xs text-slate-500 mt-1 truncate">📍 {{ auth()->user()->branch->name }}</p>
                @endif
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>

                @if(auth()->user()->isSuperAdmin())
                {{-- ═══ SUPER ADMIN ONLY ═══ --}}
                <a href="{{ route('admin.barbershops.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.barbershops.*') ? 'active' : '' }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Barbershop
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Kelola User
                </a>
                <a href="{{ route('admin.branches.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Cabang
                </a>
                @else
                {{-- ═══ ADMIN (Tenant & Single-Branch) ═══ --}}
                @if(auth()->user()->isTenantAdmin())
                {{-- Tenant Admin: Kelola semua cabang --}}
                <a href="{{ route('admin.branches.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Kelola Cabang
                </a>
                @else
                {{-- Single Branch Admin: profil cabang saja --}}
                @if(auth()->user()->branch_id)
                <a href="{{ route('admin.profile.branch') }}"
                   class="sidebar-link {{ request()->routeIs('admin.profile.branch') ? 'active' : '' }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Profil Cabang Saya
                </a>
                @endif
                @endif
                @endif

                {{-- ═══ SHARED: Monitor + Statistik ═══ --}}
                <a href="{{ route('admin.queues.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.queues.*') ? 'active' : '' }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Monitor Antrian
                    <span id="live-queue-badge" class="ml-auto bg-blue-500 text-white text-xs rounded-full px-1.5 py-0.5 min-w-[1.25rem] text-center">0</span>
                </a>

                <a href="{{ route('admin.stats.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.stats.*') ? 'active' : '' }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Statistik
                </a>

                {{-- ═══ SHARED: Scanner QR (non super admin) ═══ --}}
                @if(!auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.scanner.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.scanner.*') ? 'active' : '' }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    Scan QR Pelanggan
                </a>
                @if(auth()->user()->branch_id && !auth()->user()->isTenantAdmin())
                <a href="{{ route('admin.branches.qrcode', auth()->user()->branch_id) }}"
                   class="sidebar-link {{ request()->routeIs('admin.branches.qrcode') && !request()->routeIs('admin.branches.index') ? 'active' : '' }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    QR Cabang Saya
                </a>
                @endif
                @endif
            </nav>

            <!-- Logout -->
            <div class="p-3 border-t border-slate-200 shrink-0">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="sidebar-link w-full text-red-500 hover:bg-red-50 hover:text-red-600">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
            <!-- Top Bar -->
            <header class="glass shrink-0 sticky top-0 z-30">
                <div class="px-4 sm:px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button id="sidebar-toggle" onclick="openSidebar()" class="text-slate-400 hover:text-slate-700 transition-colors p-1 rounded-lg hover:bg-slate-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <h1 class="text-base font-semibold text-slate-800">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500 font-medium px-3 py-1 rounded-full bg-slate-100">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            Live
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 text-sm font-semibold text-red-500 hover:text-red-600 transition-colors bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg border border-red-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span class="hidden sm:inline">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            @if(session('success'))
            <div class="px-4 sm:px-6 mt-4">
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            </div>
            @endif
            @if(session('error'))
            <div class="px-4 sm:px-6 mt-4">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ session('error') }}
                </div>
            </div>
            @endif

            <main class="flex-1 overflow-y-auto">
                <div class="px-4 sm:px-6 py-4 sm:py-6 lg:py-8 max-w-full">
                    @yield('content')
                </div>
            </main>
        </div>

    </div><!-- end page wrapper -->

    <script>
        function openSidebar() {
            document.getElementById('sidebar').classList.add('open');
            document.getElementById('mobile-overlay').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('mobile-overlay').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Update live queue badge in sidebar
        function updateSidebarBadge() {
            fetch('{{ route("admin.api.monitor") }}')
                .then(r => r.json())
                .then(data => {
                    const badge = document.getElementById('live-queue-badge');
                    if (badge) badge.textContent = data.total;
                })
                .catch(() => {});
        }
        updateSidebarBadge();
        setInterval(updateSidebarBadge, 10000);
    </script>

    @stack('scripts')
</body>
</html>
