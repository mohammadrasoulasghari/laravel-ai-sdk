<?php

declare(strict_types=1);

namespace App\Shared\Http\Problems;

use App\Shared\Exceptions\DomainException;
use App\Shared\Http\Middleware\AssignCorrelationId;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Http\Request;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Maps any exception to a ProblemDetails response. Registered once in bootstrap/app.php.
 * User-facing titles come from lang/<locale>/problems.php.
 */
final readonly class ProblemDetailsFactory
{
    public function __construct(
        private Translator $translator,
        private Config $config,
        private Context $context,
    ) {}

    public function fromThrowable(Throwable $exception, Request $request): ProblemDetails
    {
        $instance = '/'.mb_ltrim($request->path(), '/');

        $extensions = array_filter([
            'correlation_id' => $this->context->get(AssignCorrelationId::CONTEXT_KEY),
        ]);

        return match (true) {
            $exception instanceof ValidationException => new ProblemDetails(
                status: Response::HTTP_UNPROCESSABLE_ENTITY,
                title: $this->title(Response::HTTP_UNPROCESSABLE_ENTITY),
                detail: $this->translate('problems.validation_failed'),
                instance: $instance,
                extensions: [...$extensions, 'errors' => $exception->errors()],
            ),
            $exception instanceof AuthenticationException => new ProblemDetails(
                status: Response::HTTP_UNAUTHORIZED,
                title: $this->title(Response::HTTP_UNAUTHORIZED),
                instance: $instance,
                extensions: $extensions,
            ),
            $exception instanceof DomainException => new ProblemDetails(
                status: $exception->status(),
                title: $this->title($exception->status()),
                detail: $this->translate($exception->translationKey(), $exception->translationParameters()),
                type: 'urn:problem:'.$exception->errorCode(),
                instance: $instance,
                extensions: $extensions,
            ),
            $exception instanceof HttpExceptionInterface => new ProblemDetails(
                status: $exception->getStatusCode(),
                title: $this->title($exception->getStatusCode()),
                instance: $instance,
                extensions: $extensions,
            ),
            default => new ProblemDetails(
                status: Response::HTTP_INTERNAL_SERVER_ERROR,
                title: $this->title(Response::HTTP_INTERNAL_SERVER_ERROR),
                detail: $this->config->get('app.debug') === true ? $exception->getMessage() : null,
                instance: $instance,
                extensions: $extensions,
            ),
        };
    }

    private function title(int $status): string
    {
        $key = 'problems.status.'.$status;
        $title = $this->translate($key);

        return $title === $key ? (Response::$statusTexts[$status] ?? 'Error') : $title;
    }

    /** @param  array<string, string|int>  $replace */
    private function translate(string $key, array $replace = []): string
    {
        $value = $this->translator->get($key, $replace);

        return is_string($value) ? $value : $key;
    }
}
