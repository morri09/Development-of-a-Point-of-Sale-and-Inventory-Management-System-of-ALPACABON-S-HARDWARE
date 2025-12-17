<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">User Permissions</h1>
            <p class="text-sm text-slate-500 mt-1">Toggle access for each sidebar page</p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-4 alert-success">
            <p class="text-sm font-medium">{{ session('message') }}</p>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 alert-danger">
            <p class="text-sm font-medium">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Permissions Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider sticky left-0 bg-slate-50 min-w-[200px]">
                            User
                        </th>
                        @foreach($menuItems as $key => $item)
                            <th class="px-3 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider min-w-[100px]">
                                {{ $item['label'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 sticky left-0 bg-white">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-sm">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500">
                                            @if($user->userRole)
                                                {{ $user->userRole->display_name }}
                                            @else
                                                No Role
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>
                            @foreach($menuItems as $key => $item)
                                <td class="px-3 py-3 text-center">
                                    @if($user->isAdmin())
                                        <svg class="w-5 h-5 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @else
                                        <button 
                                            wire:click="togglePermission({{ $user->id }}, '{{ $key }}')"
                                            class="w-6 h-6 rounded border-2 flex items-center justify-center mx-auto transition-colors {{ in_array($key, $user->userRole?->permissions ?? []) ? 'bg-indigo-600 border-indigo-600' : 'border-slate-300 hover:border-indigo-400' }}"
                                        >
                                            @if(in_array($key, $user->userRole?->permissions ?? []))
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            @endif
                                        </button>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($menuItems) + 1 }}" class="text-center py-8">
                                <p class="text-slate-500">No users found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if ($users->hasPages())
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif

    <p class="mt-4 text-sm text-slate-500">
        <span class="text-slate-400">Note:</span> Administrators have access to all pages. To change permissions, assign a different role to the user.
    </p>
</div>
