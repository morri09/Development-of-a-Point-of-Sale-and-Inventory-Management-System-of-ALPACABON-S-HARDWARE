<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="close"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">
                    Manage Permissions
                </h3>

                @if ($user)
                    <p class="text-sm text-gray-500 mb-4">
                        Configure menu access for <span class="font-medium">{{ $user->name }}</span>
                    </p>

                    @if ($user->isAdmin())
                        <div class="mb-4 p-4 bg-purple-50 border border-purple-200 rounded-md">
                            <div class="flex">
                                <svg class="h-5 w-5 text-purple-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <p class="ml-3 text-sm text-purple-800">
                                    Administrators have access to all menu items regardless of these settings.
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($saved)
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                            <div class="flex">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <p class="ml-3 text-sm font-medium text-green-800">Permissions saved successfully.</p>
                            </div>
                        </div>
                    @endif

                    <!-- Quick Actions -->
                    <div class="flex gap-2 mb-4">
                        <button
                            type="button"
                            wire:click="selectAll"
                            class="text-xs text-indigo-600 hover:text-indigo-800"
                        >
                            Select All
                        </button>
                        <span class="text-gray-300">|</span>
                        <button
                            type="button"
                            wire:click="deselectAll"
                            class="text-xs text-indigo-600 hover:text-indigo-800"
                        >
                            Deselect All
                        </button>
                    </div>

                    <!-- Permissions Checklist -->
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @foreach ($menuItems as $key => $item)
                            <label class="flex items-center p-3 bg-gray-50 rounded-md hover:bg-gray-100 cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:click="togglePermission('{{ $key }}')"
                                    @checked(in_array($key, $permissions))
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                />
                                <div class="ml-3 flex-1">
                                    <span class="text-sm font-medium text-gray-900">{{ $item['label'] }}</span>
                                    @if ($item['admin_only'] ?? false)
                                        <span class="ml-2 px-2 py-0.5 text-xs bg-purple-100 text-purple-800 rounded">Admin Only</span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-400">{{ $key }}</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">Loading user...</p>
                @endif
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button
                    type="button"
                    wire:click="save"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                    wire:loading.attr="disabled"
                    @if (!$user) disabled @endif
                >
                    <span wire:loading.remove wire:target="save">Save Permissions</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
                <button
                    type="button"
                    wire:click="close"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
