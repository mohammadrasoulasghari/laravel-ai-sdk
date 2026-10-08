<?php

declare(strict_types=1);

it('renders the login page in Persian', function (): void {
    $this->withSession(['locale' => 'fa'])
        ->get(route('login'))
        ->assertOk()
        ->assertSee('<html lang="fa" dir="rtl">', false)
        ->assertSee(__('auth::auth.login_title', locale: 'fa'))
        ->assertSee(__('auth::auth.forgot_password', locale: 'fa'))
        ->assertDontSee(__('auth::auth.login_title', locale: 'en'));
});

it('renders the login page in English', function (): void {
    $this->withSession(['locale' => 'en'])
        ->get(route('login'))
        ->assertOk()
        ->assertSee('<html lang="en" dir="ltr">', false)
        ->assertSee(__('auth::auth.login_title', locale: 'en'))
        ->assertDontSee(__('auth::auth.login_title', locale: 'fa'));
});

it('translates the invalid credentials flash for Persian users', function (): void {
    $this->withSession(['locale' => 'fa'])
        ->from(route('login'))
        ->post(route('login'), ['email' => 'nobody@example.com', 'password' => 'password'])
        ->assertSessionHas('flash.message', __('auth::auth.errors.invalid_credentials', locale: 'fa'));
});

it('translates API problems for Persian clients', function (): void {
    $this->postJson('/api/v1/auth/tokens', ['email' => 'nobody@example.com', 'password' => 'x', 'device_name' => 'cli'], ['Accept-Language' => 'fa'])
        ->assertStatus(422)
        ->assertJsonPath('detail', __('auth::auth.errors.invalid_credentials', locale: 'fa'));
});

it('renders every auth page in both locales', function (string $locale, string $route, string $titleKey): void {
    $this->withSession(['locale' => $locale])
        ->get(route($route))
        ->assertOk()
        ->assertSee(__($titleKey, locale: $locale));
})->with(['fa', 'en'])->with([
    ['login', 'auth::auth.login_title'],
    ['register', 'auth::auth.register_title'],
    ['password.request', 'auth::auth.forgot_title'],
]);
