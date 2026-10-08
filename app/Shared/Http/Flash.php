<?php

declare(strict_types=1);

namespace App\Shared\Http;

/**
 * Standard flash payload rendered by <x-ui.flash>. Usage:
 *   return to_route('blog.posts.index')->with(Flash::success(__('blog::posts.created')));
 */
final class Flash
{
    public const string SESSION_KEY = 'flash';

    /** @return array<string, array{type: string, message: string}> */
    public static function success(string $message): array
    {
        return self::make(FlashType::Success, $message);
    }

    /** @return array<string, array{type: string, message: string}> */
    public static function error(string $message): array
    {
        return self::make(FlashType::Error, $message);
    }

    /** @return array<string, array{type: string, message: string}> */
    public static function info(string $message): array
    {
        return self::make(FlashType::Info, $message);
    }

    /** @return array<string, array{type: string, message: string}> */
    public static function warning(string $message): array
    {
        return self::make(FlashType::Warning, $message);
    }

    /** @return array<string, array{type: string, message: string}> */
    private static function make(FlashType $type, string $message): array
    {
        return [self::SESSION_KEY => ['type' => $type->value, 'message' => $message]];
    }
}
