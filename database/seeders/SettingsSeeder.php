<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Default store settings for Alpacabon's Hardwarezone.
     *
     * @var array<string, string>
     */
    protected array $defaultSettings = [
        'store_name' => "Alpacabon's Hardwarezone",
        'store_address' => '7A Casino St. PAB Tagoloan, Mis. Or.',
        'store_contact' => '0965-2618254',
        'store_email' => '',
        'tax_rate' => '12',
        'receipt_footer' => 'Thank you for shopping!',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->defaultSettings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
