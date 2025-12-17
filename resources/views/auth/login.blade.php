<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'Alpacabon\'s Hardwarezone POS') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo-alpacabon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-white">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <img src="{{ asset('logo-alpacabon.png') }}" alt="Alpacabon's Hardware" class="h-48 mx-auto mb-4 object-contain">
                <p class="text-slate-500 text-sm">Point of Sale and Inventory Management System</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-lg shadow-md p-8">
                <h2 class="text-xl font-semibold text-slate-900 text-center mb-6">Sign In</h2>

                <x-validation-errors class="mb-4" />

                @session('status')
                    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-md">
                        <p class="text-sm text-emerald-600">{{ $value }}</p>
                    </div>
                @endsession

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-3.5 py-2.5 text-sm text-slate-900 bg-white border border-slate-300 rounded-lg shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
                            placeholder="Enter your email">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <input id="password" type="password" name="password" required
                            class="w-full px-3.5 py-2.5 text-sm text-slate-900 bg-white border border-slate-300 rounded-lg shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
                            placeholder="Enter your password">
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="flex items-center">
                            <input id="remember_me" type="checkbox" name="remember" 
                                class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-slate-600">Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-700">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <button type="submit" 
                        class="w-full py-2.5 px-4 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-sm shadow-indigo-200 transition-colors">
                        Sign In
                    </button>
                </form>
            </div>

            <p class="text-center text-slate-400 text-sm mt-6">
                &copy; {{ date('Y') }} Alpacabon's Hardware
            </p>
        </div>
    </div>

    @livewireScripts
</body>
</html>
