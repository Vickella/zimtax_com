<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ZimTax Compliance') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Dark theme form + table fix (keeps inputs readable on dark UI) --}}
    <style>
        /* ---------- Dark UI Inputs (Bootstrap + Tailwind safe) ---------- */
        .erp-dark .form-control,
        .erp-dark .form-select,
        .erp-dark input[type="text"],
        .erp-dark input[type="number"],
        .erp-dark input[type="date"],
        .erp-dark input[type="datetime-local"],
        .erp-dark input[type="email"],
        .erp-dark input[type="password"],
        .erp-dark input[type="search"],
        .erp-dark textarea,
        .erp-dark select {
            background-color: rgba(255,255,255,0.06) !important;
            color: rgba(255,255,255,0.92) !important;
            border: 1px solid rgba(255,255,255,0.18) !important;
        }

        .erp-dark ::placeholder {
            color: rgba(255,255,255,0.55) !important;
        }

        .erp-dark .form-control:focus,
        .erp-dark .form-select:focus,
        .erp-dark input:focus,
        .erp-dark textarea:focus,
        .erp-dark select:focus {
            outline: none !important;
            background-color: rgba(255,255,255,0.08) !important;
            border-color: rgba(108, 99, 255, 0.85) !important;
            box-shadow: 0 0 0 0.20rem rgba(108, 99, 255, 0.20) !important;
        }

        /* Dropdown options must remain readable */
        .erp-dark select option {
            color: #0b1220 !important;
        }

        /* ---------- Better table readability on dark background ---------- */
        .erp-dark table th {
            color: rgba(255,255,255,0.85);
        }
        .erp-dark table td {
            color: rgba(255,255,255,0.85);
        }
        .erp-dark .table-wrap {
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(0,0,0,0.12);
        }

        /* ---------- Custom scrollbar for dark theme ---------- */
        .scrollable-content::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .scrollable-content::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.05);
            border-radius: 4px;
        }

        .scrollable-content::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 4px;
        }

        .scrollable-content::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>

<body class="erp-dark text-slate-100 antialiased">
    <!-- Full page gradient background -->
    <div class="fixed inset-0 bg-gradient-to-br from-slate-950 via-indigo-950 to-purple-950 -z-10"></div>
    
    <!-- Main layout container -->
    <div class="min-h-screen flex flex-col lg:flex-row">
        {{-- Sidebar - fixed on desktop, hidden on mobile by default --}}
        <aside class="w-full lg:w-64 lg:min-h-screen lg:fixed lg:left-0 lg:top-0 border-b lg:border-b-0 lg:border-r border-white/10 bg-black/15 backdrop-blur-sm">
            <div class="h-16 flex items-center gap-3 px-4 border-b border-white/10">
                <img
                    src="{{ asset('build/assets/images/logo.png') }}"
                    alt="{{ config('app.name', 'ZimTax Compliance') }}"
                    class="h-9 w-9 rounded-full ring-1 ring-white/15 object-cover"
                    onerror="this.src='https://via.placeholder.com/36/2d3748/ffffff?text=ZT'"
                />
                <div class="leading-tight">
                    <div class="font-semibold text-sm">{{ config('app.name', 'ZimTax Compliance') }}</div>
                    <div class="text-[11px] text-slate-300">ERP</div>
                </div>
            </div>

            <nav class="px-2 py-3 overflow-y-auto max-h-[calc(100vh-4rem)]">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-white/5 transition-colors {{ request()->routeIs('dashboard') ? 'bg-white/10 ring-1 ring-white/10' : '' }}">
                    <span class="w-6 text-center">🏠</span>
                    <span class="truncate">Home</span>
                </a>

                <div class="mt-3 text-[11px] uppercase tracking-wider text-slate-400 px-3">Modules</div>

                <div class="mt-2 space-y-1">
                    @foreach(($modules ?? []) as $m)
                        @php
                            $active = request()->route('module') === $m['key'];
                        @endphp

                        <a href="{{ route('modules.index', ['module' => $m['key']]) }}
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors
                                  {{ $active ? 'bg-white/10 ring-1 ring-white/10' : 'hover:bg-white/5' }}">
                            <span class="w-6 text-center">{{ $m['icon'] }}</span>
                            <span class="truncate">{{ $m['name'] }}</span>
                        </a>
                    @endforeach
                </div>
            </nav>
        </aside>

        {{-- Main Content Area - with left padding for desktop sidebar --}}
        <div class="flex-1 flex flex-col min-h-screen lg:pl-64">
            {{-- Topbar --}}
            <header class="h-16 flex items-center justify-between px-5 border-b border-white/10 bg-black/10 backdrop-blur sticky top-0 z-10">
                <div class="flex items-center gap-3">
                    {{-- Mobile menu toggle (optional) --}}
                    <button class="lg:hidden p-2 rounded-lg hover:bg-white/10" onclick="toggleMobileMenu()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div class="text-sm font-semibold">@yield('page_title', 'Home')</div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-xs text-slate-300 hidden sm:block">
                        {{ auth()->user()->name ?? 'User' }}
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg px-3 py-2 text-xs bg-white/10 hover:bg-white/15 ring-1 ring-white/10 transition-colors">
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            {{-- Scrollable Content Area --}}
            <main class="flex-1 p-5 scrollable-content overflow-y-auto" style="max-height: calc(100vh - 4rem);">
                @yield('content')
            </main>

            {{-- Optional Footer --}}
            <footer class="py-3 px-5 text-xs text-slate-400 border-t border-white/5">
                &copy; {{ date('Y') }} {{ config('app.name', 'ZimTax Compliance') }}. All rights reserved.
            </footer>
        </div>
    </div>

    {{-- Mobile menu script (simple version) --}}
    <script>
        function toggleMobileMenu() {
            const sidebar = document.querySelector('aside');
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('fixed');
            sidebar.classList.toggle('inset-0');
            sidebar.classList.toggle('z-50');
            sidebar.classList.toggle('bg-black/95');
        }

        // Close mobile menu when clicking outside (optional)
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('aside');
            const menuButton = document.querySelector('button.lg\\:hidden');
            
            if (window.innerWidth < 1024 && 
                !sidebar.contains(event.target) && 
                !menuButton.contains(event.target) &&
                !sidebar.classList.contains('hidden')) {
                toggleMobileMenu();
            }
        });
    </script>
</body>
</html>