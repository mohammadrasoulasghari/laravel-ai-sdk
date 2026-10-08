<?php

declare(strict_types=1);

namespace App\Modules\Settings\Enums;

enum SettingPermission: string
{
    case Manage = 'settings.manage';
}
