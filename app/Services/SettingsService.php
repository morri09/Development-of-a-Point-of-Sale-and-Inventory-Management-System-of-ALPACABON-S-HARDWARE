<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Cache key prefix for settings.
     */
    protected const CACHE_PREFIX = 'settings.';

    /**
     * Cache duration in seconds (1 hour).
     */
    protected const CACHE_TTL = 3600;

    /**
     * Get a setting value by key with caching.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(
            self::CACHE_PREFIX . $key,
            self::CACHE_TTL,
            fn () => Setting::get($key, $default)
        );
    }

    /**
     * Set a setting value by key and update cache.
     *
     * @param string $key
     * @param mixed $value
     * @return Setting
     */
    public function set(string $key, mixed $value): Setting
    {
        $setting = Setting::set($key, $value);

        // Update cache with new value
        Cache::put(self::CACHE_PREFIX . $key, $value, self::CACHE_TTL);

        return $setting;
    }

    /**
     * Get multiple settings at once.
     *
     * @param array<string> $keys
     * @param array<string, mixed> $defaults
     * @return array<string, mixed>
     */
    public function getMany(array $keys, array $defaults = []): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $defaults[$key] ?? null);
        }

        return $result;
    }

    /**
     * Set multiple settings at once.
     *
     * @param array<string, mixed> $settings
     * @return void
     */
    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Clear cache for a specific setting.
     *
     * @param string $key
     * @return bool
     */
    public function clearCache(string $key): bool
    {
        return Cache::forget(self::CACHE_PREFIX . $key);
    }

    /**
     * Clear all settings cache.
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        $settings = Setting::all();

        foreach ($settings as $setting) {
            Cache::forget(self::CACHE_PREFIX . $setting->key);
        }
    }

    /**
     * Check if a setting exists.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return Setting::has($key);
    }

    /**
     * Remove a setting and clear its cache.
     *
     * @param string $key
     * @return bool
     */
    public function remove(string $key): bool
    {
        $this->clearCache($key);

        return Setting::remove($key);
    }

    /**
     * Get all store-related settings.
     *
     * @return array<string, mixed>
     */
    public function getStoreSettings(): array
    {
        return $this->getMany([
            'store_name',
            'store_address',
            'store_contact',
            'store_email',
            'tax_rate',
            'receipt_footer',
        ], [
            'store_name' => 'Store Name',
            'store_address' => '',
            'store_contact' => '',
            'store_email' => '',
            'tax_rate' => '12',
            'receipt_footer' => 'Thank you for shopping!',
        ]);
    }
}
