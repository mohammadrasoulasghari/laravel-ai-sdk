<?php

declare(strict_types=1);

use App\Modules\Roles\Enums\Role;
use App\Modules\Settings\Contracts\SettingsStore;
use App\Modules\Settings\Enums\SettingKey;
use App\Modules\Settings\Enums\SettingPermission;
use App\Modules\Settings\Models\Setting;
use App\Shared\Auth\SystemAbility;
use App\Shared\Http\Flash;

use function Tests\userWithPermissions;
use function Tests\userWithRole;

it('shows the current values', function (): void {
    $this->actingAs(userWithRole(Role::Admin->value))
        ->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertSee(__('settings::settings.title'))
        ->assertSee(__('settings::settings.keys.registration_open.label'))
        ->assertSee(__('settings::settings.keys.support_email.label'))
        ->assertSee('value="support@example.com"', false)
        ->assertViewHas('values', [
            SettingKey::RegistrationOpen->value => true,
            SettingKey::SupportEmail->value => 'support@example.com',
        ]);
});

it('shows stored values instead of defaults', function (): void {
    Setting::query()->create(['key' => SettingKey::SupportEmail->value, 'value' => 'help@acme.test']);
    Setting::query()->create(['key' => SettingKey::RegistrationOpen->value, 'value' => false]);

    $this->actingAs(userWithRole(Role::Admin->value))
        ->get(route('admin.settings.edit'))
        ->assertSee('value="help@acme.test"', false)
        ->assertViewHas('values', [
            SettingKey::RegistrationOpen->value => false,
            SettingKey::SupportEmail->value => 'help@acme.test',
        ]);
});

it('persists settings and busts the cache', function (): void {
    $store = resolve(SettingsStore::class);

    // Warm the cache first so a stale read would be detected.
    expect($store->string(SettingKey::SupportEmail))->toBe('support@example.com')
        ->and($store->boolean(SettingKey::RegistrationOpen))->toBeTrue();

    $this->actingAs(userWithRole(Role::Admin->value))
        ->put(route('admin.settings.update'), [
            SettingKey::RegistrationOpen->value => '0',
            SettingKey::SupportEmail->value => 'help@acme.test',
        ])
        ->assertRedirect(route('admin.settings.edit'))
        ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'success', 'message' => __('settings::settings.updated')]);

    expect(Setting::query()->findOrFail(SettingKey::SupportEmail->value)->value)->toBe('help@acme.test')
        ->and(Setting::query()->findOrFail(SettingKey::RegistrationOpen->value)->value)->toBeFalse()
        ->and($store->string(SettingKey::SupportEmail))->toBe('help@acme.test')
        ->and($store->boolean(SettingKey::RegistrationOpen))->toBeFalse();
});

it('treats a missing checkbox as off', function (): void {
    $this->actingAs(userWithRole(Role::Admin->value))
        ->put(route('admin.settings.update'), [SettingKey::SupportEmail->value => 'help@acme.test'])
        ->assertSessionHasNoErrors();

    expect(resolve(SettingsStore::class)->boolean(SettingKey::RegistrationOpen))->toBeFalse();
});

it('turns registration back on', function (): void {
    Setting::query()->create(['key' => SettingKey::RegistrationOpen->value, 'value' => false]);

    $this->actingAs(userWithRole(Role::Admin->value))
        ->put(route('admin.settings.update'), [
            SettingKey::RegistrationOpen->value => '1',
            SettingKey::SupportEmail->value => 'support@example.com',
        ]);

    expect(resolve(SettingsStore::class)->boolean(SettingKey::RegistrationOpen))->toBeTrue();

    $this->post(route('logout'));
    $this->get(route('register'))->assertOk();
});

it('updates existing rows instead of duplicating them', function (): void {
    $admin = userWithRole(Role::Admin->value);

    foreach (['one@acme.test', 'two@acme.test'] as $email) {
        $this->actingAs($admin)->put(route('admin.settings.update'), [SettingKey::SupportEmail->value => $email]);
    }

    expect(Setting::query()->count())->toBe(count(SettingKey::cases()))
        ->and(resolve(SettingsStore::class)->string(SettingKey::SupportEmail))->toBe('two@acme.test');
});

it('validates settings', function (array $payload, string $field): void {
    $this->actingAs(userWithRole(Role::Admin->value))
        ->from(route('admin.settings.edit'))
        ->put(route('admin.settings.update'), [
            SettingKey::RegistrationOpen->value => '1',
            SettingKey::SupportEmail->value => 'help@acme.test',
            ...$payload,
        ])
        ->assertRedirect(route('admin.settings.edit'))
        ->assertSessionHasErrors($field);

    expect(Setting::query()->count())->toBe(0);
})->with([
    'missing email' => [[SettingKey::SupportEmail->value => ''], SettingKey::SupportEmail->value],
    'invalid email' => [[SettingKey::SupportEmail->value => 'nope'], SettingKey::SupportEmail->value],
    'long email' => [[SettingKey::SupportEmail->value => str_repeat('a', 250).'@acme.test'], SettingKey::SupportEmail->value],
    'non boolean flag' => [[SettingKey::RegistrationOpen->value => 'maybe'], SettingKey::RegistrationOpen->value],
]);

it('uses translated attribute names in validation messages', function (): void {
    $this->actingAs(userWithRole(Role::Admin->value))
        ->put(route('admin.settings.update'), [SettingKey::SupportEmail->value => 'nope'])
        ->assertSessionHasErrors([
            SettingKey::SupportEmail->value => __('validation.email', ['attribute' => __('settings::settings.keys.support_email.label')]),
        ]);
});

it('forbids users without settings.manage', function (): void {
    $user = userWithPermissions(SystemAbility::AccessAdmin->value);

    $this->actingAs($user)->get(route('admin.settings.edit'))->assertForbidden();
    $this->actingAs($user)
        ->put(route('admin.settings.update'), [SettingKey::SupportEmail->value => 'evil@acme.test'])
        ->assertForbidden();

    expect(Setting::query()->count())->toBe(0);
});

it('forbids members', function (): void {
    $this->actingAs(userWithRole(Role::Member->value))
        ->get(route('admin.settings.edit'))
        ->assertForbidden();
});

it('redirects guests', function (): void {
    $this->get(route('admin.settings.edit'))->assertRedirect(route('login'));
    $this->put(route('admin.settings.update'), [])->assertRedirect(route('login'));
});

it('lets users with exactly the needed permissions in', function (): void {
    $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value, SettingPermission::Manage->value))
        ->get(route('admin.settings.edit'))
        ->assertOk();
});

it('lets super admins manage settings', function (): void {
    $this->actingAs(userWithRole(Role::SuperAdmin->value))
        ->put(route('admin.settings.update'), [SettingKey::SupportEmail->value => 'root@acme.test'])
        ->assertRedirect(route('admin.settings.edit'));

    expect(resolve(SettingsStore::class)->string(SettingKey::SupportEmail))->toBe('root@acme.test');
});
