<?php

declare(strict_types=1);

namespace App\Modules\Settings\Queries;

use App\Modules\Settings\Contracts\SettingsStore;
use App\Modules\Settings\Enums\SettingKey;
use App\Modules\Settings\Models\Setting;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Reads all settings in one query and caches them forever under CACHE_KEY.
 * UpdateSettings forgets the key. See docs/caching.md.
 */
final readonly class SettingsQuery implements SettingsStore
{
    public const string CACHE_KEY = 'settings:all';

    public function __construct(private Cache $cache) {}

    public function all(): array
    {
        /** @var array<string, mixed> $stored */
        $stored = $this->cache->rememberForever(
            self::CACHE_KEY,
            static fn (): array => Setting::query()->pluck('value', 'key')->all(),
        );

        $values = [];

        foreach (SettingKey::cases() as $key) {
            $value = $stored[$key->value] ?? $key->default();
            $values[$key->value] = $key->isBoolean() ? (bool) $value : (is_scalar($value) ? (string) $value : (string) $key->default());
        }

        return $values;
    }

    public function string(SettingKey $key): string
    {
        return (string) $this->all()[$key->value];
    }

    public function boolean(SettingKey $key): bool
    {
        return (bool) $this->all()[$key->value];
    }
}
