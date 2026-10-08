<?php

declare(strict_types=1);

use App\Shared\Auth\SystemAbility;
use Illuminate\Support\Facades\Gate;

use function Tests\userWithPermissions;
use function Tests\userWithRole;

it('grants dashboard gates only with the system tools permission', function (string $gate): void {
    $member = userWithRole('member');
    $operator = userWithPermissions(SystemAbility::ViewSystemTools->value);

    expect(Gate::forUser($member)->allows($gate))->toBeFalse()
        ->and(Gate::forUser($operator)->allows($gate))->toBeTrue()
        ->and(Gate::allows($gate))->toBeFalse();
})->with(['viewPulse', 'viewApiDocs', 'viewHorizon']);

it('hides the OpenAPI document from users without the permission outside local', function (): void {
    $this->actingAs(userWithRole('member'))->getJson('/docs/api.json')->assertForbidden();
});

it('serves the OpenAPI document to operators', function (): void {
    $this->actingAs(userWithPermissions(SystemAbility::ViewSystemTools->value))
        ->getJson('/docs/api.json')
        ->assertOk()
        ->assertJsonPath('openapi', fn (string $version): bool => str_starts_with($version, '3.'))
        ->assertJsonStructure(['paths' => ['/v1/auth/tokens', '/v1/me']]);
});
