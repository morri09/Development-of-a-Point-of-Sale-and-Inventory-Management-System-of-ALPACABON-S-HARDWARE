<div class="max-w-4xl mx-auto">
    @if ($saved)
        <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl p-4 animate-fade-in">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                    <x-lucide-check-circle class="w-5 h-5 text-emerald-600" />
                </div>
                <div>
                    <p class="font-medium text-emerald-800">Settings saved successfully!</p>
                    <p class="text-sm text-emerald-600">Your changes have been applied.</p>
                </div>
            </div>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <!-- Store Information Section -->
        <div class="card">
            <div class="px-6 py-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <x-lucide-store class="w-5 h-5 text-indigo-600" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Store Information</h2>
                        <p class="text-sm text-slate-500">Basic details about your business</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-5">
                <!-- Store Name -->
                <div>
                    <label for="store_name" class="form-label">
                        Store Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-lucide-building-2 class="w-5 h-5 text-slate-400" />
                        </div>
                        <input type="text" id="store_name" wire:model="store_name" class="form-input pl-10" placeholder="e.g., Alpacabon's Hardwarezone" />
                    </div>
                    @error('store_name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <!-- Store Address -->
                <div>
                    <label for="store_address" class="form-label">Store Address</label>
                    <div class="relative">
                        <div class="absolute top-3 left-3 pointer-events-none">
                            <x-lucide-map-pin class="w-5 h-5 text-slate-400" />
                        </div>
                        <textarea id="store_address" wire:model="store_address" rows="2" class="form-input pl-10" placeholder="Enter your complete store address"></textarea>
                    </div>
                    @error('store_address') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="card">
            <div class="px-6 py-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <x-lucide-contact class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Contact Information</h2>
                        <p class="text-sm text-slate-500">How customers can reach you</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Contact Number -->
                    <div>
                        <label for="store_contact" class="form-label">Phone Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-lucide-phone class="w-5 h-5 text-slate-400" />
                            </div>
                            <input type="text" id="store_contact" wire:model="store_contact" class="form-input pl-10" placeholder="e.g., 0965-2618254" />
                        </div>
                        @error('store_contact') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label for="store_email" class="form-label">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-lucide-mail class="w-5 h-5 text-slate-400" />
                            </div>
                            <input type="email" id="store_email" wire:model="store_email" class="form-input pl-10" placeholder="e.g., store@example.com" />
                        </div>
                        @error('store_email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Settings Section -->
        <div class="card">
            <div class="px-6 py-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                        <x-lucide-calculator class="w-5 h-5 text-amber-600" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Business Settings</h2>
                        <p class="text-sm text-slate-500">Tax and transaction preferences</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-5">
                <!-- Tax Rate -->
                <div class="max-w-xs">
                    <label for="tax_rate" class="form-label">
                        Tax Rate <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-lucide-percent class="w-5 h-5 text-slate-400" />
                        </div>
                        <input type="number" id="tax_rate" wire:model="tax_rate" step="0.01" min="0" max="100" class="form-input pl-10 pr-12" placeholder="12" />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-slate-500 font-medium">%</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Applied to all transactions</p>
                    @error('tax_rate') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Receipt Settings Section -->
        <div class="card">
            <div class="px-6 py-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <x-lucide-receipt class="w-5 h-5 text-emerald-600" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Receipt Settings</h2>
                        <p class="text-sm text-slate-500">Customize your printed receipts</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <!-- Receipt Footer -->
                <div>
                    <label for="receipt_footer" class="form-label">Footer Message</label>
                    <textarea id="receipt_footer" wire:model="receipt_footer" rows="3" class="form-input" placeholder="e.g., Thank you for shopping! Visit us again."></textarea>
                    <p class="text-xs text-slate-500 mt-1">This message appears at the bottom of every receipt</p>
                    @error('receipt_footer') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex items-center justify-between pt-2">
            <p class="text-sm text-slate-500">
                <span class="text-red-500">*</span> Required fields
            </p>
            <button type="submit" class="btn-primary px-6" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed">
                <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                    <x-lucide-save class="w-5 h-5" />
                    Save Changes
                </span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <x-lucide-loader-2 class="w-5 h-5 animate-spin" />
                    Saving...
                </span>
            </button>
        </div>
    </form>
</div>
