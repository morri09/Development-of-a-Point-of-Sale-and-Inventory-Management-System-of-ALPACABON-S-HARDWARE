@props(['mobile' => false])

@php
    $menuItems = config('menu.items', []);
    $menuGroups = config('menu.groups', []);
    $user = auth()->user();
    $userPermissions = $user?->getPermittedMenuItems() ?? [];
    $isAdmin = $user?->isAdmin() ?? false;
    
    $canSeeItem = function ($key, $item) use ($user, $userPermissions, $isAdmin) {
        if ($isAdmin) return true;
        if ($item['admin_only'] ?? false) return false;
        return in_array($key, $userPermissions, true);
    };
    
    $visibleGroups = collect($menuGroups)->map(function ($group) use ($menuItems, $canSeeItem) {
        $visibleItems = collect($group['items'])->filter(function ($key) use ($menuItems, $canSeeItem) {
            return isset($menuItems[$key]) && $canSeeItem($key, $menuItems[$key]);
        });
        return ['label' => $group['label'], 'items' => $visibleItems];
    })->filter(fn ($group) => $group['items']->isNotEmpty());

    $routeUrls = [];
    foreach ($menuItems as $key => $item) {
        $routeUrls[$key] = route($item['route']);
    }
@endphp

@if($mobile)
<div 
    x-data="{ 
        open: false,
        currentPath: window.location.pathname,
        routeUrls: {{ Js::from($routeUrls) }},
        isActive(key) {
            const url = this.routeUrls[key];
            if (!url) return false;
            const urlPath = new URL(url).pathname;
            return this.currentPath === urlPath || (urlPath !== '/' && this.currentPath.startsWith(urlPath + '/'));
        }
    }" 
    class="lg:hidden"
>
    <button @click="open = !open" class="fixed top-3.5 left-3 z-50 p-2.5 rounded-xl bg-white shadow-lg shadow-slate-200/50 text-slate-600 hover:text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all" aria-label="Toggle menu">
        <x-lucide-menu x-show="!open" class="h-5 w-5" />
        <x-lucide-x x-show="open" x-cloak class="h-5 w-5" />
    </button>

    <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 z-40 bg-slate-900/20 backdrop-blur-sm transition-opacity" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    <aside x-show="open" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 z-50 w-72 bg-gradient-to-b from-blue-600 to-blue-800 shadow-2xl shadow-blue-900/20 transform">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-center py-6 px-4 border-b border-blue-500/30">
                <img src="{{ asset('logo.png') }}" alt="Hardwarezone Logo" class="w-full max-w-[200px] object-contain" />
            </div>
            <nav class="flex-1 px-3 py-4 overflow-y-auto scrollbar-hide">
                @foreach($visibleGroups as $groupKey => $group)
                    @if(!$loop->first)<div class="my-3 mx-2 border-t border-blue-500/30"></div>@endif
                    @if($group['label'])<div class="px-3 py-2"><span class="text-xs font-semibold text-blue-300 uppercase tracking-wider">{{ $group['label'] }}</span></div>@endif
                    <div class="space-y-1">
                        @foreach($group['items'] as $key)
                            @php $item = $menuItems[$key]; @endphp
                            <a href="{{ route($item['route']) }}" @click="open = false" :class="isActive('{{ $key }}') ? 'bg-white/20 text-white shadow-sm' : 'text-blue-100 hover:bg-white/10 hover:text-white'" class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150">
                                <x-sidebar-icon :icon="$item['icon']" class="h-5 w-5 transition-colors" ::class="isActive('{{ $key }}') ? 'text-white' : 'text-blue-300 group-hover:text-white'" />
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>
        </div>
    </aside>
</div>
@else
<aside 
    x-data="{ 
        currentPath: window.location.pathname, 
        routeUrls: {{ Js::from($routeUrls) }}, 
        isActive(key) { 
            const url = this.routeUrls[key]; 
            if (!url) return false; 
            const urlPath = new URL(url).pathname; 
            return this.currentPath === urlPath || (urlPath !== '/' && this.currentPath.startsWith(urlPath + '/')); 
        }
    }" 
    class="hidden lg:block fixed inset-y-0 left-0 z-40 w-64"
>
    <div class="flex flex-col h-full bg-gradient-to-b from-blue-600 to-blue-800">
        <div class="flex items-center justify-center py-6 px-4 border-b border-blue-500/30">
            <img src="{{ asset('logo.png') }}" alt="Hardwarezone Logo" class="w-full max-w-[180px] object-contain" />
        </div>
        <nav class="flex-1 px-3 py-4 overflow-y-auto scrollbar-hide">
            @foreach($visibleGroups as $groupKey => $group)
                @if(!$loop->first)<div class="my-3 mx-2 border-t border-blue-500/30"></div>@endif
                @if($group['label'])<div class="px-3 py-2"><span class="text-xs font-semibold text-blue-300 uppercase tracking-wider">{{ $group['label'] }}</span></div>@endif
                <div class="space-y-1">
                    @foreach($group['items'] as $key)
                        @php $item = $menuItems[$key]; @endphp
                        <a href="{{ route($item['route']) }}" :class="isActive('{{ $key }}') ? 'bg-white/20 text-white shadow-sm' : 'text-blue-100 hover:bg-white/10 hover:text-white'" class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150">
                            <x-sidebar-icon :icon="$item['icon']" class="h-5 w-5 transition-colors" ::class="isActive('{{ $key }}') ? 'text-white' : 'text-blue-300 group-hover:text-white'" />
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>
    </div>
</aside>
@endif
