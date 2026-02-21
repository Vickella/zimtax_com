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
        .form-control,
        .form-select,
        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="datetime-local"],
        input[type="email"],
        input[type="password"],
        input[type="search"],
        textarea,
        select {
            background-color: rgba(255,255,255,0.06) !important;
            color: rgba(255,255,255,0.92) !important;
            border: 1px solid rgba(255,255,255,0.18) !important;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
        }

        ::placeholder {
            color: rgba(255,255,255,0.55) !important;
        }

        .form-control:focus,
        .form-select:focus,
        input:focus,
        textarea:focus,
        select:focus {
            outline: none !important;
            background-color: rgba(255,255,255,0.08) !important;
            border-color: rgba(108, 99, 255, 0.85) !important;
            box-shadow: 0 0 0 0.20rem rgba(108, 99, 255, 0.20) !important;
        }

        /* Dropdown options must remain readable */
        select option {
            color: #0b1220 !important;
            background: white;
        }

        /* ---------- Better table readability on dark background ---------- */
        table th {
            color: rgba(255,255,255,0.85);
            font-weight: 600;
        }
        
        table td {
            color: rgba(255,255,255,0.85);
        }
        
        .table-wrap {
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(0,0,0,0.12);
            border-radius: 0.75rem;
            overflow: hidden;
        }

        /* ---------- Custom scrollbar for dark theme ---------- */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.05);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Firefox scrollbar */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.2) rgba(255,255,255,0.05);
        }

        /* ---------- Ensure scrolling works ---------- */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        .scrollable-content {
            overflow-y: auto;
            overflow-x: hidden;
            height: 100%;
        }

        .content-wrapper {
            min-height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Table horizontal scroll */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 1rem;
        }

        /* Card styles */
        .card {
            background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 0.75rem;
            padding: 1.25rem;
        }

        /* Form layout */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .p-responsive {
                padding: 1rem;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-indigo-950 via-slate-950 to-purple-950 text-slate-100 antialiased">
    <!-- Main container with flex column to push footer down -->
    <div class="min-h-screen flex flex-col">
        <!-- Header/Navigation -->
        <header class="bg-black/20 border-b border-white/10 sticky top-0 z-50 backdrop-blur-sm">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo and brand -->
                    <div class="flex items-center gap-3">
                        <div class="font-semibold text-lg">{{ config('app.name', 'ZimTax') }}</div>
                    </div>

                    <!-- User menu -->
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-300 hidden sm:inline">
                            {{ auth()->user()->name ?? 'Guest' }}
                        </span>
                        @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm bg-white/10 hover:bg-white/15 px-3 py-1.5 rounded-lg transition-colors">
                                Logout
                            </button>
                        </form>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <!-- Main content - THIS WILL SCROLL -->
        <main class="flex-1 overflow-y-auto">
            <div class="px-4 sm:px-6 lg:px-8 py-6">
                <!-- Page title -->
                @hasSection('page_title')
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold">@yield('page_title')</h1>
                </div>
                @endif

                <!-- Content area - forms and tables will scroll here -->
                <div class="scrollable-content min-h-[calc(100vh-12rem)]">
                    @yield('content')
                </div>
            </div>
        </main>

        <!-- Footer (optional) -->
        <footer class="bg-black/20 border-t border-white/10 py-3 px-4 sm:px-6 lg:px-8 text-xs text-slate-400">
            <div class="flex justify-between">
                <span>&copy; {{ date('Y') }} {{ config('app.name', 'ZimTax') }}</span>
                <span>v1.0.0</span>
            </div>
        </footer>
    </div>

    <!-- Mobile menu script (if needed) -->
    <script>
        // Add any JavaScript here if needed
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure all scrollable containers work
            const mainContent = document.querySelector('main');
            if (mainContent) {
                mainContent.style.overflowY = 'auto';
            }
        });
    </script>
</body>
</html>