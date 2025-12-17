<?php

namespace App\Livewire;

use App\Services\SettingsService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app-with-sidebar')]
class StoreSettings extends Component
{
    public string $store_name = '';
    public string $store_address = '';
    public string $store_contact = '';
    public string $store_email = '';
    public string $tax_rate = '';
    public string $receipt_footer = '';

    public bool $saved = false;

    protected SettingsService $settingsService;

    protected array $rules = [
        'store_name' => 'required|string|max:255',
        'store_address' => 'nullable|string|max:500',
        'store_contact' => 'nullable|string|max:50',
        'store_email' => 'nullable|email|max:255',
        'tax_rate' => 'required|numeric|min:0|max:100',
        'receipt_footer' => 'nullable|string|max:500',
    ];

    protected array $messages = [
        'store_name.required' => 'Store name is required.',
        'store_name.max' => 'Store name cannot exceed 255 characters.',
        'store_email.email' => 'Please enter a valid email address.',
        'tax_rate.required' => 'Tax rate is required.',
        'tax_rate.numeric' => 'Tax rate must be a number.',
        'tax_rate.min' => 'Tax rate cannot be negative.',
        'tax_rate.max' => 'Tax rate cannot exceed 100%.',
    ];

    public function boot(SettingsService $settingsService): void
    {
        $this->settingsService = $settingsService;
    }

    public function mount(): void
    {
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        $settings = app(SettingsService::class)->getStoreSettings();

        $this->store_name = $settings['store_name'] ?? '';
        $this->store_address = $settings['store_address'] ?? '';
        $this->store_contact = $settings['store_contact'] ?? '';
        $this->store_email = $settings['store_email'] ?? '';
        $this->tax_rate = $settings['tax_rate'] ?? '12';
        $this->receipt_footer = $settings['receipt_footer'] ?? '';
    }

    public function save(): void
    {
        $this->validate();

        $settingsService = app(SettingsService::class);

        $settingsService->setMany([
            'store_name' => $this->store_name,
            'store_address' => $this->store_address,
            'store_contact' => $this->store_contact,
            'store_email' => $this->store_email,
            'tax_rate' => $this->tax_rate,
            'receipt_footer' => $this->receipt_footer,
        ]);

        $this->saved = true;

        $this->dispatch('settings-saved');
    }

    public function render()
    {
        return view('livewire.store-settings');
    }
}
