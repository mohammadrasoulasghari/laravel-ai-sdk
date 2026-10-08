<?php

declare(strict_types=1);

return [
    'validation_failed' => 'The given data was invalid.',
    'status' => [
        400 => 'Bad request',
        401 => 'Unauthenticated',
        403 => 'Forbidden',
        404 => 'Not found',
        405 => 'Method not allowed',
        409 => 'Conflict',
        419 => 'Page expired',
        422 => 'Unprocessable content',
        429 => 'Too many requests',
        500 => 'Server error',
        503 => 'Service unavailable',
    ],
];
