<?php

namespace Tests\Property;

use App\Models\Setting;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature: pos-hardwarezone, Property 1: Settings Round-Trip Consistency
 * Validates: Requirements 1.2, 1.3
 * 
 * For any store setting key-value pair, saving it to the database and then 
 * retrieving it should return the same value.
 */
class SettingsPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    /**
     * Property 1: Settings Round-Trip Consistency
     * 
     * For any store setting key-value pair, saving it to the database and then
     * retrieving it should return the same value, and any page displaying that
     * setting should show the stored value.
     */
    #[Test]
    public function settings_round_trip_consistency(): void
    {
        $this->forAll(
            Generators::string(),  // key
            Generators::string()   // value
        )
        ->withMaxSize(100)
        ->__invoke(function (string $key, string $value): void {
            // Skip empty keys as they are not valid setting keys
            if (empty(trim($key))) {
                return;
            }

            // Save the setting to the database
            Setting::set($key, $value);

            // Retrieve the setting from the database
            $retrievedValue = Setting::get($key);

            // Assert round-trip consistency: saved value equals retrieved value
            $this->assertSame(
                $value,
                $retrievedValue,
                "Setting round-trip failed: saved '{$value}' but retrieved '{$retrievedValue}' for key '{$key}'"
            );

            // Clean up for next iteration
            Setting::remove($key);
        });
    }

    /**
     * Property 1 (Extended): Settings update preserves round-trip consistency
     * 
     * For any setting that is updated, the new value should be retrievable.
     */
    #[Test]
    public function settings_update_round_trip_consistency(): void
    {
        $this->forAll(
            Generators::string(),  // key
            Generators::string(),  // initial value
            Generators::string()   // updated value
        )
        ->withMaxSize(100)
        ->__invoke(function (string $key, string $initialValue, string $updatedValue): void {
            // Skip empty keys
            if (empty(trim($key))) {
                return;
            }

            // Save initial value
            Setting::set($key, $initialValue);
            
            // Update with new value
            Setting::set($key, $updatedValue);

            // Retrieve should return the updated value
            $retrievedValue = Setting::get($key);

            $this->assertSame(
                $updatedValue,
                $retrievedValue,
                "Setting update round-trip failed: updated to '{$updatedValue}' but retrieved '{$retrievedValue}'"
            );

            // Clean up
            Setting::remove($key);
        });
    }

    /**
     * Property 1 (Default Value): Missing settings return default value
     * 
     * For any non-existent setting key, retrieving it should return the provided default.
     */
    #[Test]
    public function missing_settings_return_default_value(): void
    {
        $this->forAll(
            Generators::string(),  // key
            Generators::string()   // default value
        )
        ->withMaxSize(100)
        ->__invoke(function (string $key, string $defaultValue): void {
            // Skip empty keys
            if (empty(trim($key))) {
                return;
            }

            // Ensure the key doesn't exist
            Setting::remove($key);

            // Retrieve with default should return the default
            $retrievedValue = Setting::get($key, $defaultValue);

            $this->assertSame(
                $defaultValue,
                $retrievedValue,
                "Missing setting should return default '{$defaultValue}' but got '{$retrievedValue}'"
            );
        });
    }
}
