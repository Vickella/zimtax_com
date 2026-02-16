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
    </style>
</head>

<body class="erp-dark h-screen overflow-hidden text-slate-100">
<div class="h-screen w-screen bg-gradient-to-br from-slate-950 via-indigo-950 to-purple-950">
    <div class="h-screen flex">

        {{-- Sidebar (fixed, no scroll) --}}
        <aside class="w-64 shrink-0 border-r border-white/10 bg-black/15">
            <div class="h-16 flex items-center gap-3 px-4 border-b border-white/10">
                <img
                    src="{{ asset('build/assets/images/logo.png') }}"
                    alt="{{ config('app.name', 'ZimTax Compliance') }}"
                    class="h-9 w-9 rounded-full ring-1 ring-white/15 object-cover"
                />
                <div class="leading-tight">
                    <div class="font-semibold text-sm">{{ config('app.name', 'ZimTax Compliance') }}</div>
                    <div class="text-[11px] text-slate-300">ERP</div>
                </div>
            </div>

            <nav class="px-2 py-3">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-white/5 {{ request()->routeIs('dashboard') ? 'bg-white/10 ring-1 ring-white/10' : '' }}">
                    🏠 <span class="truncate">Home</span>
                </a>

                <div class="mt-3 text-[11px] uppercase tracking-wider text-slate-400 px-3">Modules</div>

                <div class="mt-2 space-y-1">
                    @foreach(($modules ?? []) as $m)
                        @php
                            $active = request()->route('module') === $m['key'];
                        @endphp

                        <a href="{{ route('modules.index', ['module' => $m['key']]) }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm
                                  {{ $active ? 'bg-white/10 ring-1 ring-white/10' : 'hover:bg-white/5' }}">
                            <span class="w-6 text-center">{{ $m['icon'] }}</span>
                            <span class="truncate">{{ $m['name'] }}</span>
                        </a>
                    @endforeach
                </div>
            </nav>
        </aside>

        {{-- Main --}}
        <div class="flex-1 min-w-0">
            {{-- Topbar --}}
            <header class="h-16 flex items-center justify-between px-5 border-b border-white/10 bg-black/10 backdrop-blur">
                <div class="text-sm font-semibold">@yield('page_title', 'Home')</div>

                <div class="flex items-center gap-3">
                    <div class="text-xs text-slate-300 hidden sm:block">
                        {{ auth()->user()->name ?? 'User' }}
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg px-3 py-2 text-xs bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            {{-- Content area (fixed height, internal scroll only where needed) --}}
            <main class="h-[calc(100vh-4rem)] overflow-auto p-5">
                @yield('content')
            </main>
        </div>

    </div>
</div>
</body>
</html>
