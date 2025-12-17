<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Alpacabon\'s Hardwarezone POS') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('logo-alpacabon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles

        <style>
            [x-cloak] { display: none !important; }
            
            /* Navigation Progress Bar */
            @keyframes progress-bar {
                0% { transform: translateX(-100%); }
                50% { transform: translateX(0%); }
                100% { transform: translateX(100%); }
            }
            .animate-progress-bar {
                animation: progress-bar 1s ease-in-out infinite;
            }
            
            /* Preloader Styles */
            .typewriter {
                --blue: #3b82f6;
                --blue-dark: #1d4ed8;
                --key: #e2e8f0;
                --paper: #EEF0FD;
                --text: #D3D4EC;
                --tool: #FBC56C;
                --duration: 3s;
                position: relative;
                animation: bounce05 var(--duration) linear infinite;
            }
            .typewriter .slide {
                width: 92px; height: 20px; border-radius: 3px; margin-left: 14px;
                transform: translateX(14px);
                background: linear-gradient(var(--blue), var(--blue-dark));
                animation: slide05 var(--duration) ease infinite;
            }
            .typewriter .slide:before, .typewriter .slide:after, .typewriter .slide i:before {
                content: ""; position: absolute; background: var(--tool);
            }
            .typewriter .slide:before { width: 2px; height: 8px; top: 6px; left: 100%; }
            .typewriter .slide:after { left: 94px; top: 3px; height: 14px; width: 6px; border-radius: 3px; }
            .typewriter .slide i { display: block; position: absolute; right: 100%; width: 6px; height: 4px; top: 4px; background: var(--tool); }
            .typewriter .slide i:before { right: 100%; top: -2px; width: 4px; border-radius: 2px; height: 14px; }
            .typewriter .paper {
                position: absolute; left: 24px; top: -26px; width: 40px; height: 46px;
                border-radius: 5px; background: var(--paper); transform: translateY(46px);
                animation: paper05 var(--duration) linear infinite;
            }
            .typewriter .paper:before {
                content: ""; position: absolute; left: 6px; right: 6px; top: 7px;
                border-radius: 2px; height: 4px; transform: scaleY(0.8); background: var(--text);
                box-shadow: 0 12px 0 var(--text), 0 24px 0 var(--text), 0 36px 0 var(--text);
            }
            .typewriter .keyboard { width: 120px; height: 56px; margin-top: -10px; z-index: 1; position: relative; }
            .typewriter .keyboard:before, .typewriter .keyboard:after { content: ""; position: absolute; }
            .typewriter .keyboard:before {
                top: 0; left: 0; right: 0; bottom: 0; border-radius: 7px;
                background: linear-gradient(135deg, var(--blue), var(--blue-dark));
                transform: perspective(10px) rotateX(2deg); transform-origin: 50% 100%;
            }
            .typewriter .keyboard:after {
                left: 2px; top: 25px; width: 11px; height: 4px; border-radius: 2px;
                box-shadow: 15px 0 0 var(--key), 30px 0 0 var(--key), 45px 0 0 var(--key), 60px 0 0 var(--key), 75px 0 0 var(--key), 90px 0 0 var(--key), 22px 10px 0 var(--key), 37px 10px 0 var(--key), 52px 10px 0 var(--key), 60px 10px 0 var(--key), 68px 10px 0 var(--key), 83px 10px 0 var(--key);
                animation: keyboard05 var(--duration) linear infinite;
            }
            @keyframes bounce05 { 85%, 92%, 100% { transform: translateY(0); } 89% { transform: translateY(-4px); } 95% { transform: translateY(2px); } }
            @keyframes slide05 { 5% { transform: translateX(14px); } 15%, 30% { transform: translateX(6px); } 40%, 55% { transform: translateX(0); } 65%, 70% { transform: translateX(-4px); } 80%, 89% { transform: translateX(-12px); } 100% { transform: translateX(14px); } }
            @keyframes paper05 { 5% { transform: translateY(46px); } 20%, 30% { transform: translateY(34px); } 40%, 55% { transform: translateY(22px); } 65%, 70% { transform: translateY(10px); } 80%, 85% { transform: translateY(0); } 92%, 100% { transform: translateY(46px); } }
            @keyframes keyboard05 {
                5%, 12%, 21%, 30%, 39%, 48%, 57%, 66%, 75%, 84% { box-shadow: 15px 0 0 var(--key), 30px 0 0 var(--key), 45px 0 0 var(--key), 60px 0 0 var(--key), 75px 0 0 var(--key), 90px 0 0 var(--key), 22px 10px 0 var(--key), 37px 10px 0 var(--key), 52px 10px 0 var(--key), 60px 10px 0 var(--key), 68px 10px 0 var(--key), 83px 10px 0 var(--key); }
                9% { box-shadow: 15px 2px 0 var(--key), 30px 0 0 var(--key), 45px 0 0 var(--key), 60px 0 0 var(--key), 75px 0 0 var(--key), 90px 0 0 var(--key), 22px 10px 0 var(--key), 37px 10px 0 var(--key), 52px 10px 0 var(--key), 60px 10px 0 var(--key), 68px 10px 0 var(--key), 83px 10px 0 var(--key); }
                18% { box-shadow: 15px 0 0 var(--key), 30px 0 0 var(--key), 45px 0 0 var(--key), 60px 2px 0 var(--key), 75px 0 0 var(--key), 90px 0 0 var(--key), 22px 10px 0 var(--key), 37px 10px 0 var(--key), 52px 10px 0 var(--key), 60px 10px 0 var(--key), 68px 10px 0 var(--key), 83px 10px 0 var(--key); }
                27% { box-shadow: 15px 0 0 var(--key), 30px 0 0 var(--key), 45px 0 0 var(--key), 60px 0 0 var(--key), 75px 0 0 var(--key), 90px 0 0 var(--key), 22px 12px 0 var(--key), 37px 10px 0 var(--key), 52px 10px 0 var(--key), 60px 10px 0 var(--key), 68px 10px 0 var(--key), 83px 10px 0 var(--key); }
                36% { box-shadow: 15px 0 0 var(--key), 30px 0 0 var(--key), 45px 0 0 var(--key), 60px 0 0 var(--key), 75px 0 0 var(--key), 90px 0 0 var(--key), 22px 10px 0 var(--key), 37px 10px 0 var(--key), 52px 12px 0 var(--key), 60px 12px 0 var(--key), 68px 12px 0 var(--key), 83px 10px 0 var(--key); }
                45% { box-shadow: 15px 0 0 var(--key), 30px 0 0 var(--key), 45px 0 0 var(--key), 60px 0 0 var(--key), 75px 0 0 var(--key), 90px 2px 0 var(--key), 22px 10px 0 var(--key), 37px 10px 0 var(--key), 52px 10px 0 var(--key), 60px 10px 0 var(--key), 68px 10px 0 var(--key), 83px 10px 0 var(--key); }
                54% { box-shadow: 15px 0 0 var(--key), 30px 2px 0 var(--key), 45px 0 0 var(--key), 60px 0 0 var(--key), 75px 0 0 var(--key), 90px 0 0 var(--key), 22px 10px 0 var(--key), 37px 10px 0 var(--key), 52px 10px 0 var(--key), 60px 10px 0 var(--key), 68px 10px 0 var(--key), 83px 10px 0 var(--key); }
                63% { box-shadow: 15px 0 0 var(--key), 30px 0 0 var(--key), 45px 0 0 var(--key), 60px 0 0 var(--key), 75px 0 0 var(--key), 90px 0 0 var(--key), 22px 10px 0 var(--key), 37px 10px 0 var(--key), 52px 10px 0 var(--key), 60px 10px 0 var(--key), 68px 10px 0 var(--key), 83px 12px 0 var(--key); }
                72% { box-shadow: 15px 0 0 var(--key), 30px 0 0 var(--key), 45px 2px 0 var(--key), 60px 0 0 var(--key), 75px 0 0 var(--key), 90px 0 0 var(--key), 22px 10px 0 var(--key), 37px 10px 0 var(--key), 52px 10px 0 var(--key), 60px 10px 0 var(--key), 68px 10px 0 var(--key), 83px 10px 0 var(--key); }
                81% { box-shadow: 15px 0 0 var(--key), 30px 0 0 var(--key), 45px 0 0 var(--key), 60px 0 0 var(--key), 75px 0 0 var(--key), 90px 0 0 var(--key), 22px 10px 0 var(--key), 37px 12px 0 var(--key), 52px 10px 0 var(--key), 60px 10px 0 var(--key), 68px 10px 0 var(--key), 83px 10px 0 var(--key); }
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-800">
        {{-- Preloader --}}
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 800)" x-show="show" x-cloak
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-white"
        >
            <div class="typewriter">
                <div class="slide"><i></i></div>
                <div class="paper"></div>
                <div class="keyboard"></div>
            </div>
        </div>
        
        <x-banner />

        <div class="min-h-screen flex">
            {{-- Desktop Sidebar --}}
            <x-sidebar />

            {{-- Mobile Sidebar --}}
            <x-sidebar :mobile="true" />

            {{-- Main Content Area --}}
            <div class="flex-1 flex flex-col min-w-0 lg:ml-64">
                {{-- Top Header --}}
                <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-sm border-b border-slate-200">
                    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                        {{-- Page Title --}}
                        <div class="flex items-center">
                            {{-- Spacer for mobile hamburger --}}
                            <div class="w-12 lg:hidden"></div>
                            @php
                                $pageTitle = $header ?? null;
                                if (!$pageTitle) {
                                    $menuItems = config('menu.items', []);
                                    foreach ($menuItems as $key => $item) {
                                        if (request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*')) {
                                            $pageTitle = $item['label'];
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            @if ($pageTitle)
                                <h1 class="text-lg font-semibold text-slate-900 tracking-tight">
                                    {{ $pageTitle }}
                                </h1>
                            @endif
                        </div>

                        {{-- User Dropdown --}}
                        <div class="flex items-center gap-3">
                            {{-- Low Stock Notification Dropdown --}}
                            @php
                                $lowStockProducts = \App\Models\Product::where('is_active', true)
                                    ->where('stock_quantity', '>', 0)
                                    ->where('stock_quantity', '<=', 10)
                                    ->orderBy('stock_quantity', 'asc')
                                    ->limit(5)
                                    ->get();
                                $lowStockCount = \App\Models\Product::where('is_active', true)
                                    ->where('stock_quantity', '>', 0)
                                    ->where('stock_quantity', '<=', 10)
                                    ->count();
                            @endphp
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="relative p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" title="{{ $lowStockCount }} low stock items">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                    </svg>
                                    @if($lowStockCount > 0)
                                        <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                            {{ $lowStockCount > 9 ? '9+' : $lowStockCount }}
                                        </span>
                                    @endif
                                </button>

                                {{-- Notification Dropdown Panel --}}
                                <div x-show="open" @click.away="open = false" x-cloak
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-slate-200 z-50"
                                >
                                    <div class="px-4 py-3 border-b border-slate-100">
                                        <h3 class="text-sm font-semibold text-slate-900">Low Stock Alerts</h3>
                                        <p class="text-xs text-slate-500">{{ $lowStockCount }} product(s) running low</p>
                                    </div>

                                    <div class="max-h-64 overflow-y-auto">
                                        @forelse($lowStockProducts as $product)
                                            <div class="px-4 py-3 hover:bg-slate-50 border-b border-slate-50 last:border-0">
                                                <div class="flex items-start gap-3">
                                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-slate-900 truncate">{{ $product->name }}</p>
                                                        <p class="text-xs text-slate-500">
                                                            Only <span class="font-semibold text-amber-600">{{ $product->stock_quantity }}</span> left in stock
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="px-4 py-6 text-center">
                                                <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <p class="text-sm text-slate-500">All products are well stocked!</p>
                                            </div>
                                        @endforelse
                                    </div>

                                    @if($lowStockCount > 0)
                                        <div class="px-4 py-3 border-t border-slate-100 bg-slate-50 rounded-b-xl">
                                            <a href="{{ route('inventory.index') }}" class="block text-center text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                                View all in Inventory →
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- User Menu --}}
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="flex items-center gap-2 p-1.5 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                                        <img class="h-8 w-8 rounded-full object-cover ring-2 ring-slate-100" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                        <span class="hidden sm:block max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                                        <svg class="hidden sm:block w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <div class="px-4 py-3 border-b border-slate-100">
                                        <p class="text-sm font-medium text-slate-900">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
                                    </div>

                                    <div class="py-1">
                                        <x-dropdown-link href="{{ route('profile.show') }}" class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                            </svg>
                                            {{ __('Profile') }}
                                        </x-dropdown-link>
                                    </div>

                                    <div class="border-t border-slate-100">
                                        <form method="POST" action="{{ route('logout') }}" x-data>
                                            @csrf
                                            <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();" class="flex items-center gap-2 text-red-600 hover:text-red-700 hover:bg-red-50">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                                </svg>
                                                {{ __('Log Out') }}
                                            </x-dropdown-link>
                                        </form>
                                    </div>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </header>

                {{-- Page Content --}}
                <main class="flex-1 overflow-y-auto">
                    <div class="p-4 sm:p-6 lg:p-8">
                        {{ $slot }}
                    </div>
                </main>

                {{-- Footer --}}
                <footer class="border-t border-slate-200 bg-white px-4 sm:px-6 lg:px-8 py-4">
                    <p class="text-xs text-slate-500 text-center">
                        &copy; {{ date('Y') }} Alpacabon's Hardwarezone. All rights reserved.
                    </p>
                </footer>
            </div>
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>
