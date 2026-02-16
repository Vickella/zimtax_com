<x-app-layout>
    <div class="min-h-[calc(100vh-64px)] bg-gradient-to-br from-indigo-950 via-violet-950 to-slate-950">
        <div class="mx-auto max-w-[1400px] px-4 py-6">
            <div class="grid grid-cols-12 gap-6">

                {{-- LEFT: Fixed sidebar (no scrolling, compact) --}}
                <aside class="col-span-12 lg:col-span-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur-xl shadow-xl h-[calc(100vh-120px)]">
                        <div class="flex items-center gap-3 px-5 py-4 border-b border-white/10">
                            <img src="{{ asset('build/assets/images/logo.png') }}" class="h-10 w-10 object-contain" alt="Logo">
                            <div class="min-w-0">
                                <div class="text-white font-semibold leading-tight truncate">ZimTax Compliance</div>
                                <div class="text-white/60 text-xs truncate">Compliance-first ERP</div>
                            </div>
                        </div>

                        <div class="px-3 py-3">
                            <div class="text-white/70 text-xs px-2 mb-2">Modules</div>

                            {{-- compact height so everything fits without scroll --}}
                            <div class="space-y-2">
                                @foreach($modules as $m)
                                    <a href="{{ route('modules.index', ['module' => $m['key']]) }}"
                                       class="flex items-center justify-between rounded-xl px-3 py-2 border border-white/10 bg-white/5 hover:bg-white/10 hover:border-white/20 transition">
                                        <div class="min-w-0">
                                            <div class="text-white text-sm font-medium truncate">{{ $m['label'] }}</div>
                                            <div class="text-white/60 text-[11px] truncate">{{ $m['description'] }}</div>
                                        </div>
                                        <div class="text-white/50 text-xs shrink-0">Open</div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </aside>

                {{-- RIGHT: Main --}}
                <main class="col-span-12 lg:col-span-9">
                    <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur-xl shadow-xl">
                        <div class="px-6 py-5 border-b border-white/10">
                            <div class="text-white text-xl font-semibold">Dashboard</div>
                            <div class="text-white/60 text-sm mt-1">Quick actions, shortcuts and key operational cards.</div>
                        </div>

                        <div class="p-6 space-y-6">

                            {{-- Cards --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach($cards as $c)
                                    <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                                        <div class="text-white/60 text-xs">{{ $c['label'] }}</div>
                                        <div class="text-white text-2xl font-semibold mt-1">{{ $c['value'] }}</div>
                                        <div class="text-white/60 text-xs mt-2">{{ $c['hint'] }}</div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Shortcuts --}}
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                                <div class="flex items-center justify-between">
                                    <div class="text-white font-semibold">Shortcuts</div>
                                    <div class="text-white/60 text-xs">Quick actions</div>
                                </div>

                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($shortcuts as $s)
                                        <a href="{{ $s['route'] }}"
                                           class="rounded-2xl border border-white/10 bg-white/5 hover:bg-white/10 hover:border-white/20 transition p-5">
                                            <div class="text-white font-semibold">{{ $s['title'] }}</div>
                                            <div class="text-white/60 text-xs mt-2">{{ $s['subtitle'] }}</div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            {{-- ERPNext mimic: Module quick jump --}}
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                                <div class="text-white font-semibold">Quick Jump</div>
                                <div class="text-white/60 text-xs mt-1">Masters, Transactions, Reports, Settings</div>

                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($modules as $m)
                                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                                            <div class="text-white font-semibold truncate">{{ $m['label'] }}</div>
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                <a class="text-xs px-3 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white/80"
                                                   href="{{ route('modules.masters', $m['key']) }}">Masters</a>
                                                <a class="text-xs px-3 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white/80"
                                                   href="{{ route('modules.transactions', $m['key']) }}">Transactions</a>
                                                <a class="text-xs px-3 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white/80"
                                                   href="{{ route('modules.reports', $m['key']) }}">Reports</a>
                                                <a class="text-xs px-3 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white/80"
                                                   href="{{ route('modules.settings', $m['key']) }}">Settings</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>
                </main>

            </div>
        </div>
    </div>
</x-app-layout>
