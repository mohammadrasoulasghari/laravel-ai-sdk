<?php

declare(strict_types=1);

use App\Modules\Settings\Actions\UpdateSettings;
use App\Modules\Settings\Contracts\SettingsStore;
use App\Modules\Settings\Data\SettingsData;
use App\Modules\Settings\Enums\SettingKey;
use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Queries\SettingsQuery;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

it('binds the store contract to the cached query', function (): void {
    expect(resolve(SettingsStore::class))->toBeInstanceOf(SettingsQuery::class);
});

it('returns defaults when nothing is stored', function (): void {
    expect(resolve(SettingsStore::class)->all())->toBe([
        'registration_open' => true,
        'support_email' => 'support@example.com',
    ]);
});

it('reads all settings with a single cached query', function (): void {
    Setting::query()->create(['key' => SettingKey::SupportEmail->value, 'value' => 'help@acme.test']);
    $store = resolve(SettingsStore::class);

    DB::enableQueryLog();
    $store->all();
    $store->string(SettingKey::SupportEmail);
    $store->boolean(SettingKey::RegistrationOpen);

    expect(DB::getQueryLog())->toHaveCount(1)
        ->and(Cache::has(SettingsQuery::CACHE_KEY))->toBeTrue();
});

it('serves stale values until the cache is forgotten', function (): void {
    $store = resolve(SettingsStore::class);
    $store->all();

    Setting::query()->create(['key' => SettingKey::SupportEmail->value, 'value' => 'direct@acme.test']);

    expect($store->string(SettingKey::SupportEmail))->toBe('support@example.com');

    Cache::forget(SettingsQuery::CACHE_KEY);

    expect($store->string(SettingKey::SupportEmail))->toBe('direct@acme.test');
});

it('upserts values and forgets the cache', function (): void {
    $store = resolve(SettingsStore::class);
    $store->all();

    resolve(UpdateSettings::class)->handle(new SettingsData([
        SettingKey::RegistrationOpen->value => false,
        SettingKey::SupportEmail->value => 'help@acme.test',
    ]));

    expect(Cache::has(SettingsQuery::CACHE_KEY))->toBeFalse()
        ->and($store->boolean(SettingKey::RegistrationOpen))->toBeFalse()
        ->and($store->string(SettingKey::SupportEmail))->toBe('help@acme.test');

    resolve(UpdateSettings::class)->handle(new SettingsData([SettingKey::RegistrationOpen->value => true]));

    expect(Setting::query()->count())->toBe(2)
        ->and($store->boolean(SettingKey::RegistrationOpen))->toBeTrue()
        ->and($store->string(SettingKey::SupportEmail))->toBe('help@acme.test');
});

it('rejects unknown setting keys', function (): void {
    expect(fn (): null => resolve(UpdateSettings::class)->handle(new SettingsData(['nope' => 'x'])))
        ->toThrow(ValueError::class);

    expect(Setting::query()->count())->toBe(0);
});
