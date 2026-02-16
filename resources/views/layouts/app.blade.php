<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ZimTax Compliance') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gradient-to-br from-indigo-950 via-slate-950 to-purple-950 text-slate-100">
    <main class="h-screen w-screen overflow-hidden">
        @yield('content')
    </main>
</body>
</html>
