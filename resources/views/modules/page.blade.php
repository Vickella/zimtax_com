@extends('layouts.app')

@section('content')
<div class="h-screen w-screen overflow-hidden p-6">
    <div class="h-full rounded-xl border border-white/10 bg-black/20 backdrop-blur p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="text-2xl">{{ $moduleMeta['icon'] }}</div>
                <div>
                    <div class="text-xl font-semibold text-white">{{ $moduleMeta['name'] }}</div>
                    <div class="text-sm text-slate-300">
                        {{ ucfirst($section) }} / {{ ucfirst(str_replace('-', ' ', $page)) }}
                    </div>
                </div>
            </div>

            <a href="{{ route('modules.section', ['module'=>$module, 'section'=>$section]) }}"
               class="text-sm px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 transition">
                Back
            </a>
        </div>

        <div class="text-sm text-slate-200">
            Placeholder page: <span class="text-white font-semibold">{{ $page }}</span>.
        </div>
    </div>
</div>
@endsection
