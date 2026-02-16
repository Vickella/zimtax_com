<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ZimTax Compliance') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex items-center justify-center px-4 bg-gradient-to-br from-purple-700 via-indigo-700 to-blue-700">
        <div class="w-full max-w-md">
            <div class="text-center mb-6">
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center">
                    <x-application-logo />
                </a>

                <h1 class="mt-4 text-2xl font-semibold text-white">
                    Welcome to ZimTax Compliance
                </h1>
                <p class="mt-1 text-sm text-white/80">
                    Secure login to continue
                </p>
            </div>

            <div class="bg-white/95 backdrop-blur rounded-2xl shadow-xl p-6">
                {{ $slot }}
            </div>

            <p class="text-center text-xs text-white/70 mt-6">
                © {{ date('Y') }} ZimTax Compliance
            </p>
        </div>
    </div>
</body>
</html>
