<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Api;

use App\Modules\Auth\Actions\IssueApiToken;
use App\Modules\Auth\Actions\RevokeCurrentToken;
use App\Modules\Auth\Http\Requests\IssueTokenRequest;
use App\Modules\Users\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class TokenController
{
    /**
     * Issue a personal access token.
     *
     * @unauthenticated
     */
    public function store(IssueTokenRequest $request, IssueApiToken $issue): JsonResponse
    {
        $token = $issue->handle($request->toData());

        return new JsonResponse([
            'data' => [
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $token->expiresAt?->format(DATE_ATOM),
            ],
        ], Response::HTTP_CREATED);
    }

    /** Revoke the token used for this request. */
    public function destroy(#[CurrentUser] User $user, RevokeCurrentToken $revoke): Response
    {
        $revoke->handle($user);

        return response()->noContent();
    }
}
