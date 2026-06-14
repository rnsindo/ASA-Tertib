<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    public const TYPE_STRING = 'string';
    public const TYPE_TEXT = 'text';
    public const TYPE_IMAGE = 'image';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_JSON = 'json';

    protected $fillable = [
        'key',
        'group',
        'label',
        'type',
        'value',
        'options',
        'is_public',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_public' => 'boolean',
        ];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            self::TYPE_BOOLEAN => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_INTEGER => is_numeric($setting->value) ? (int) $setting->value : $default,
            self::TYPE_JSON => $setting->value ? json_decode($setting->value, true) : $default,
            default => $setting->value ?? $default,
        };
    }

    public static function putValue(string $key, mixed $value, array $attributes = []): self
    {
        $type = $attributes['type'] ?? self::TYPE_STRING;

        $storedValue = match ($type) {
            self::TYPE_JSON => json_encode($value),
            self::TYPE_BOOLEAN => $value ? '1' : '0',
            default => $value,
        };

        return static::query()->updateOrCreate(
            ['key' => $key],
            array_merge($attributes, ['value' => $storedValue]),
        );
    }
}
