@extends('layouts.app')

@section('content')
<div class="h-screen w-screen overflow-hidden">
    <div class="h-full w-full grid grid-cols-[280px_1fr]">

        {{-- LEFT: Modules --}}
        <aside class="h-full border-r border-white/10 bg-black/20 backdrop-blur">
            <div class="h-full flex flex-col">

                {{-- Logo / App title --}}
                <div class="px-5 py-4 border-b border-white/10 flex items-center gap-3">
                    <img
                        src="{{ asset('build/assets/images/logo.png') }}"
                        class="h-9 w-9 rounded-lg"
                        alt="Logo"
                    />
                    <div class="leading-tight">
                        <div class="font-semibold text-white">ZimTax Compliance</div>
                        <div class="text-xs text-slate-300">ERP & Statutory Reporting</div>
                    </div>
                </div>

                {{-- Module List (fits screen) --}}
                <div class="flex-1 px-3 py-3">
                    <div class="text-xs uppercase tracking-wider text-slate-400 px-2 mb-2">
                        Modules
                    </div>

                    <nav class="space-y-1">
                        @foreach($modules as $m)
                            <a
                                href="{{ route('modules.index', ['module' => $m['key']]) }}"
                                class="group flex items-center justify-between rounded-lg px-3 py-2
                                       text-slate-200 hover:text-white
                                       hover:bg-white/10 transition"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="text-lg">{{ $m['icon'] }}</span>
                                    <span class="text-sm font-medium">{{ $m['name'] }}</span>
                                </div>
                                <span class="text-slate-400 group-hover:text-slate-200">›</span>
                            </a>
                        @endforeach
                    </nav>
                </div>

                {{-- Footer --}}
                <div class="px-5 py-3 border-t border-white/10 text-xs text-slate-400">
                    Logged in as <span class="text-slate-200">{{ auth()->user()->name }}</span>
                </div>
            </div>
        </aside>

        {{-- RIGHT: Main (Shortcuts + Cards) --}}
        <section class="h-full overflow-hidden">
            <div class="h-full p-6">

                {{-- Header row --}}
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-xl font-semibold text-white">Dashboard</h1>
                        <p class="text-sm text-slate-300">Quick access to core actions</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('profile.edit') }}"
                           class="text-sm px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 transition">
                            Profile
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="text-sm px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 transition">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

                {{-- CONTENT grid that fits screen (no scrolling) --}}
                <div class="grid grid-cols-12 gap-4 h-[calc(100%-72px)]">

                    {{-- Shortcuts --}}
                    <div class="col-span-8 h-full">
                        <div class="h-full rounded-xl border border-white/10 bg-black/20 backdrop-blur p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-sm font-semibold text-white">Shortcuts</div>
                                <div class="text-xs text-slate-400">Common actions</div>
                            </div>

                            <div class="grid grid-cols-5 gap-3">
                                @foreach($shortcuts as $s)
                                    <a href="{{ $s['route'] }}"
                                       class="rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 transition
                                              p-3 flex flex-col items-center justify-center text-center">
                                        <div class="text-2xl mb-2">{{ $s['icon'] }}</div>
                                        <div class="text-xs font-medium text-slate-100">{{ $s['label'] }}</div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Cards --}}
                    <div class="col-span-4 h-full grid grid-rows-3 gap-4">
                        @foreach($cards as $card)
                            <div class="rounded-xl border border-white/10 bg-black/20 backdrop-blur p-4">
                                <div class="text-sm font-semibold text-white mb-2">{{ $card['title'] }}</div>
                                <ul class="space-y-1 text-sm text-slate-200">
                                    @foreach($card['items'] as $item)
                                        <li class="flex items-center justify-between">
                                            <span>{{ $item }}</span>
                                            <span class="text-slate-400">•</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </section>

    </div>
</div>
@endsection
