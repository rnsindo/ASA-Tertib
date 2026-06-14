<?php

namespace App\Support;

use App\Models\AppSetting;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AppClock
{
    public static function timezone(): string
    {
        $timezone = config('app.timezone', 'Asia/Jakarta');

        try {
            if (Schema::hasTable('app_settings')) {
                $timezone = AppSetting::getValue('app.timezone', $timezone);
            }
        } catch (Throwable) {
            $timezone = config('app.timezone', 'Asia/Jakarta');
        }

        return self::isValidTimezone($timezone) ? $timezone : 'Asia/Jakarta';
    }

    public static function applyConfiguredTimezone(): void
    {
        $timezone = self::timezone();

        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    }

    public static function now(): \Carbon\Carbon
    {
        return now(self::timezone());
    }

    public static function format(?CarbonInterface $date, string $format = 'd/m/Y H:i'): string
    {
        if (! $date) {
            return '-';
        }

        return $date->copy()->timezone(self::timezone())->format($format);
    }

    public static function isoNow(): string
    {
        return self::now()->toIso8601String();
    }

    public static function isValidTimezone(mixed $timezone): bool
    {
        return is_string($timezone) && in_array($timezone, timezone_identifiers_list(), true);
    }

    public static function timezoneOptions(): array
    {
        return [
            'Asia/Jakarta' => 'WIB - Asia/Jakarta',
            'Asia/Makassar' => 'WITA - Asia/Makassar',
            'Asia/Jayapura' => 'WIT - Asia/Jayapura',
            'UTC' => 'UTC',
        ];
    }
}
