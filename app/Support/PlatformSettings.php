<?php

namespace App\Support;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Cache;

class PlatformSettings
{
    private const CACHE_PREFIX = 'platform_setting.';

    /**
     * @return array<string, array{label: string, type: string, group: string, default: mixed}>
     */
    public static function definitions(): array
    {
        return [
            'app_display_name' => [
                'label' => 'Platform name',
                'type' => 'string',
                'group' => 'general',
                'default' => config('app.name', 'Rentkia'),
            ],
            'support_email' => [
                'label' => 'Support email',
                'type' => 'string',
                'group' => 'general',
                'default' => 'support@rentkia.com',
            ],
            'support_phone' => [
                'label' => 'Support phone',
                'type' => 'string',
                'group' => 'general',
                'default' => '',
            ],
            'currency_code' => [
                'label' => 'Currency code',
                'type' => 'string',
                'group' => 'general',
                'default' => 'INR',
            ],
            'currency_symbol' => [
                'label' => 'Currency symbol',
                'type' => 'string',
                'group' => 'general',
                'default' => '₹',
            ],
            'maintenance_mode' => [
                'label' => 'Maintenance mode',
                'type' => 'bool',
                'group' => 'general',
                'default' => false,
            ],
            'trial_days' => [
                'label' => 'Vendor free trial (days)',
                'type' => 'int',
                'group' => 'subscription',
                'default' => (int) config('subscription.trial_days', 30),
            ],
            'platform_commission_percent' => [
                'label' => 'Platform commission (%)',
                'type' => 'float',
                'group' => 'subscription',
                'default' => 0,
            ],
            'vendor_kyc_required' => [
                'label' => 'Require KYC before vendor goes live',
                'type' => 'bool',
                'group' => 'subscription',
                'default' => true,
            ],
            'min_booking_hours' => [
                'label' => 'Minimum booking notice (hours)',
                'type' => 'int',
                'group' => 'booking',
                'default' => 24,
            ],
            'cancellation_hours_before' => [
                'label' => 'Free cancellation window (hours before start)',
                'type' => 'int',
                'group' => 'booking',
                'default' => 48,
            ],
            'default_security_deposit_percent' => [
                'label' => 'Default security deposit (%)',
                'type' => 'float',
                'group' => 'booking',
                'default' => 20,
            ],
            'gst_rate_percent' => [
                'label' => 'Default GST rate (%)',
                'type' => 'float',
                'group' => 'tax',
                'default' => 18,
            ],
            'gst_enabled' => [
                'label' => 'Enable GST on orders',
                'type' => 'bool',
                'group' => 'tax',
                'default' => true,
            ],
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $definitions = self::definitions();
        $fallback = $default ?? ($definitions[$key]['default'] ?? null);

        return Cache::remember(self::CACHE_PREFIX.$key, 3600, function () use ($key, $fallback) {
            $row = PlatformSetting::find($key);

            if (! $row) {
                return $fallback;
            }

            return self::castStored($row->value, $row->type);
        });
    }

    public static function trialDays(): int
    {
        return max(1, (int) self::get('trial_days', config('subscription.trial_days', 30)));
    }

    /**
     * @return array<string, mixed>
     */
    public static function valuesForForm(): array
    {
        $values = [];

        foreach (self::definitions() as $key => $def) {
            $values[$key] = self::get($key, $def['default']);
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public static function updateMany(array $input): void
    {
        foreach (self::definitions() as $key => $def) {
            if (! array_key_exists($key, $input)) {
                if ($def['type'] === 'bool') {
                    $input[$key] = false;
                } else {
                    continue;
                }
            }

            $value = $input[$key];

            if ($def['type'] === 'bool') {
                $stored = $value ? '1' : '0';
            } elseif ($def['type'] === 'int') {
                $stored = (string) (int) $value;
            } elseif ($def['type'] === 'float') {
                $stored = (string) (float) $value;
            } else {
                $stored = (string) $value;
            }

            PlatformSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $stored,
                    'type' => $def['type'],
                    'group' => $def['group'],
                    'label' => $def['label'],
                ]
            );

            Cache::forget(self::CACHE_PREFIX.$key);
        }
    }

    public static function seedDefaults(): void
    {
        foreach (self::definitions() as $key => $def) {
            if (PlatformSetting::where('key', $key)->exists()) {
                continue;
            }

            $value = $def['default'];
            if ($def['type'] === 'bool') {
                $stored = $value ? '1' : '0';
            } else {
                $stored = (string) $value;
            }

            PlatformSetting::create([
                'key' => $key,
                'value' => $stored,
                'type' => $def['type'],
                'group' => $def['group'],
                'label' => $def['label'],
            ]);
        }
    }

    private static function castStored(?string $value, string $type): mixed
    {
        return match ($type) {
            'bool' => in_array($value, ['1', 'true', 'yes'], true),
            'int' => (int) $value,
            'float' => (float) $value,
            default => $value,
        };
    }
}
