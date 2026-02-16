@extends('layouts.app')

@section('content')
<div class="h-screen w-screen overflow-hidden p-6">
    <div class="h-full rounded-xl border border-white/10 bg-black/20 backdrop-blur p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="text-2xl">{{ $moduleMeta['icon'] }}</div>
                <div>
                    <div class="text-xl font-semibold text-white">{{ $moduleMeta['name'] }}</div>
                    <div class="text-sm text-slate-300 capitalize">{{ $section }}</div>
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('modules.index', ['module'=>$module]) }}" class="text-sm px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 transition">
                    Module Home
                </a>
                <a href="{{ route('dashboard') }}" class="text-sm px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 transition">
                    Dashboard
                </a>
            </div>
        </div>

        <div class="text-sm text-slate-200">
            Placeholder list for <span class="font-semibold text-white">{{ $moduleMeta['name'] }}</span>
            → <span class="capitalize">{{ $section }}</span>.
            <br>
            Next step: we will add the actual doctypes/pages here.
        </div>
    </div>
</div>
@endsection
