<?php

declare(strict_types=1);

namespace App\Shared\Http;

enum FlashType: string
{
    case Success = 'success';
    case Error = 'error';
    case Info = 'info';
    case Warning = 'warning';
}
