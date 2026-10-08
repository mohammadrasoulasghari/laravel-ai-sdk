<?php

declare(strict_types=1);

namespace App\Shared\Navigation;

enum NavigationArea: string
{
    case App = 'app';
    case Admin = 'admin';
}
