@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-4rem)] overflow-hidden px-6 py-5">
  <div class="text-white">
    <div class="text-lg font-semibold capitalize">{{ str_replace('-', ' ', $module) }}</div>
    <div class="text-xs text-white/70">Coming next: {{ $section ?? 'module' }} {{ $page ? ' / '.$page : '' }}</div>
  </div>
</div>
@endsection
