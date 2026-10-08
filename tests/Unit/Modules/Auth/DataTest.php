<?php

declare(strict_types=1);

use App\Modules\Auth\Data\IssuedTokenData;
use App\Modules\Auth\Data\IssueTokenData;
use App\Modules\Auth\Data\LoginData;
use App\Modules\Auth\Data\RegisterData;
use App\Modules\Auth\Data\ResetPasswordData;
use App\Modules\Auth\Http\Requests\IssueTokenRequest;
use App\Modules\Auth\Http\Requests\LoginRequest;

it('builds a login throttle key per lower-cased email and ip', function (): void {
    $key = (new LoginData('Jane@Example.com', 'secret', false, '10.0.0.1'))->throttleKey();

    expect($key)->toStartWith('login:')
        ->toBe((new LoginData('jane@example.com', 'other', true, '10.0.0.1'))->throttleKey())
        ->not->toBe((new LoginData('jane@example.com', 'secret', false, '10.0.0.2'))->throttleKey())
        ->not->toBe((new LoginData('john@example.com', 'secret', false, '10.0.0.1'))->throttleKey())
        ->not->toContain('jane');
});

it('keeps api token throttling separate from web login throttling', function (): void {
    $api = (new IssueTokenData('jane@example.com', 'secret', 'phone', '10.0.0.1'))->throttleKey();
    $web = (new LoginData('jane@example.com', 'secret', false, '10.0.0.1'))->throttleKey();

    expect($api)->toStartWith('api-token:')
        ->not->toBe($web)
        ->toBe((new IssueTokenData('JANE@example.com', 'x', 'laptop', '10.0.0.1'))->throttleKey());
});

it('hides passwords from stack traces', function (string $class, string $parameter): void {
    $constructor = new ReflectionMethod($class, '__construct');
    $param = collect($constructor->getParameters())->firstOrFail(fn (ReflectionParameter $p): bool => $p->getName() === $parameter);

    expect($param->getAttributes(SensitiveParameter::class))->toHaveCount(1);
})->with([
    [LoginData::class, 'password'],
    [IssueTokenData::class, 'password'],
    [RegisterData::class, 'password'],
    [ResetPasswordData::class, 'password'],
    [ResetPasswordData::class, 'token'],
    [IssuedTokenData::class, 'plainTextToken'],
]);

it('maps the login request to data', function (): void {
    $request = LoginRequest::create('/login', 'POST', ['email' => 'jane@example.com', 'password' => 'secret', 'remember' => '1'], server: ['REMOTE_ADDR' => '10.1.2.3']);

    $data = $request->toData();

    expect($data->email)->toBe('jane@example.com')
        ->and($data->password)->toBe('secret')
        ->and($data->remember)->toBeTrue()
        ->and($data->ip)->toBe('10.1.2.3');
});

it('maps the token request to data', function (): void {
    $request = IssueTokenRequest::create('/api/v1/auth/tokens', 'POST', ['email' => 'jane@example.com', 'password' => 'secret', 'device_name' => 'cli'], server: ['REMOTE_ADDR' => '10.1.2.3']);

    $data = $request->toData();

    expect($data->deviceName)->toBe('cli')
        ->and($data->ip)->toBe('10.1.2.3');
});
