<?php

declare(strict_types=1);

use App\Modules\Settings\Enums\SettingKey;
use App\Modules\Settings\Http\Requests\UpdateSettingsRequest;
use App\Modules\Settings\Queries\SettingsQuery;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;

function settingsQueryWith(array $stored): SettingsQuery
{
    $cache = new Repository(new ArrayStore);
    $cache->forever(SettingsQuery::CACHE_KEY, $stored);

    return new SettingsQuery($cache);
}

it('declares defaults and types for every key', function (): void {
    expect(SettingKey::RegistrationOpen->default())->toBeTrue()
        ->and(SettingKey::RegistrationOpen->isBoolean())->toBeTrue()
        ->and(SettingKey::SupportEmail->default())->toBe('support@example.com')
        ->and(SettingKey::SupportEmail->isBoolean())->toBeFalse();
});

it('declares validation rules for every key', function (): void {
    expect(SettingKey::RegistrationOpen->rules())->toBe(['boolean'])
        ->and(SettingKey::SupportEmail->rules())->toBe(['required', 'email', 'max:255']);
});

it('has labels and hints in both locales', function (SettingKey $key, string $locale): void {
    foreach (['label', 'hint'] as $part) {
        $translationKey = "settings::settings.keys.{$key->value}.{$part}";
        expect(__($translationKey, locale: $locale))->not->toBe($translationKey);
    }
})->with(SettingKey::cases())->with(['en', 'fa']);

it('reads cached values without touching the database', function (): void {
    $query = settingsQueryWith(['registration_open' => false, 'support_email' => 'help@acme.test']);

    expect($query->all())->toBe(['registration_open' => false, 'support_email' => 'help@acme.test'])
        ->and($query->boolean(SettingKey::RegistrationOpen))->toBeFalse()
        ->and($query->string(SettingKey::SupportEmail))->toBe('help@acme.test');
});

it('falls back to defaults and casts stored values', function (): void {
    expect(settingsQueryWith([])->all())->toBe(['registration_open' => true, 'support_email' => 'support@example.com'])
        ->and(settingsQueryWith(['registration_open' => 0])->boolean(SettingKey::RegistrationOpen))->toBeFalse()
        ->and(settingsQueryWith(['registration_open' => 1])->boolean(SettingKey::RegistrationOpen))->toBeTrue()
        ->and(settingsQueryWith(['support_email' => ['not', 'scalar']])->string(SettingKey::SupportEmail))->toBe('support@example.com')
        ->and(settingsQueryWith(['support_email' => 42])->string(SettingKey::SupportEmail))->toBe('42')
        ->and(settingsQueryWith(['unknown' => 'ignored'])->all())->not->toHaveKey('unknown');
});

it('builds request rules and attributes from the enum', function (): void {
    $request = new UpdateSettingsRequest;

    expect($request->rules())->toBe([
        'registration_open' => ['boolean'],
        'support_email' => ['required', 'email', 'max:255'],
    ])->and($request->attributes())->toBe([
        'registration_open' => __('settings::settings.keys.registration_open.label'),
        'support_email' => __('settings::settings.keys.support_email.label'),
    ]);
});

it('maps the request to typed settings data', function (): void {
    $data = UpdateSettingsRequest::create('/admin/settings', 'PUT', ['registration_open' => 'on', 'support_email' => 'help@acme.test'])->toData();

    expect($data->values)->toBe(['registration_open' => true, 'support_email' => 'help@acme.test']);

    $off = UpdateSettingsRequest::create('/admin/settings', 'PUT', [])->toData();

    expect($off->values)->toBe(['registration_open' => false, 'support_email' => '']);
});
