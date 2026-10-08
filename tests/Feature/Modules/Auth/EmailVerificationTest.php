<?php

declare(strict_types=1);

use App\Modules\Users\Models\User;
use App\Shared\Http\Flash;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

function verificationUrl(User $user, ?string $email = null): string
{
    return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->getKey(),
        'hash' => hash('sha1', $email ?? $user->getEmailForVerification()),
    ]);
}

it('shows the verification notice to unverified users', function (): void {
    $this->actingAs(User::factory()->unverified()->create())
        ->get(route('verification.notice'))
        ->assertOk()
        ->assertSee(__('auth::auth.verify_title'))
        ->assertSee(__('auth::auth.resend_verification'));
});

it('redirects verified users away from the notice', function (): void {
    $this->actingAs(User::factory()->create())
        ->get(route('verification.notice'))
        ->assertRedirect(route('dashboard', absolute: false));
});

it('sends unverified users from the dashboard to the notice', function (): void {
    $this->actingAs(User::factory()->unverified()->create())
        ->get(route('dashboard'))
        ->assertRedirect(route('verification.notice'));
});

it('verifies the email with a signed link', function (): void {
    Event::fake([Verified::class]);
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(verificationUrl($user))
        ->assertRedirect(route('dashboard', absolute: false))
        ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'success', 'message' => __('auth::auth.verified')]);

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
    Event::assertDispatched(Verified::class);
});

it('rejects an unsigned verification link', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('verification.verify', ['id' => $user->id, 'hash' => hash('sha1', $user->email)]))
        ->assertForbidden();

    expect($user->refresh()->hasVerifiedEmail())->toBeFalse();
});

it('rejects a link with the wrong hash', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get(verificationUrl($user, 'other@example.com'))->assertForbidden();

    expect($user->refresh()->hasVerifiedEmail())->toBeFalse();
});

it('rejects a link meant for another user', function (): void {
    $user = User::factory()->unverified()->create();
    $other = User::factory()->unverified()->create();

    $this->actingAs($user)->get(verificationUrl($other))->assertForbidden();

    expect($other->refresh()->hasVerifiedEmail())->toBeFalse();
});

it('rejects an expired verification link', function (): void {
    $user = User::factory()->unverified()->create();
    $url = verificationUrl($user);

    $this->travel(2)->hours();

    $this->actingAs($user)->get($url)->assertForbidden();
});

it('requires login to verify', function (): void {
    $user = User::factory()->unverified()->create();

    $this->get(verificationUrl($user))->assertRedirect(route('login'));
});

it('resends the verification email', function (): void {
    Notification::fake();
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->from(route('verification.notice'))
        ->post(route('verification.send'))
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'info', 'message' => __('auth::auth.verification_sent')]);

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('does not resend for verified users', function (): void {
    Notification::fake();

    $this->actingAs(User::factory()->create())
        ->post(route('verification.send'))
        ->assertRedirect(route('dashboard'));

    Notification::assertNothingSent();
});
