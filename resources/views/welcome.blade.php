<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ZimTax Compliance</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-700 via-indigo-700 to-blue-700 text-white">
    <div class="min-h-screen flex items-center justify-center px-6">
        <div class="w-full max-w-lg text-center">
            <div class="flex justify-center">
                <img src="{{ asset('assets/images/logo.png') }}" class="h-20 w-20 object-contain" alt="ZimTax Compliance">
            </div>

            <h1 class="mt-6 text-3xl font-semibold">
                ZimTax Compliance
            </h1>
            <p class="mt-2 text-white/80">
                Tax compliance and ERP-ready workflows for Zimbabwe.
            </p>

            <div class="mt-8 flex items-center justify-center gap-3">
                <a href="{{ route('login') }}"
                   class="px-5 py-2.5 rounded-xl bg-white text-gray-900 font-medium hover:bg-white/90 transition">
                    Login
                </a>

                <a href="{{ route('register') }}"
                   class="px-5 py-2.5 rounded-xl border border-white/40 bg-white/10 hover:bg-white/15 transition font-medium">
                    Register
                </a>
            </div>

            <div class="mt-5">
                <a href="{{ route('password.request') }}" class="text-sm underline text-white/80 hover:text-white">
                    Forgot password?
                </a>
            </div>
        </div>
    </div>
</body>
</html>
