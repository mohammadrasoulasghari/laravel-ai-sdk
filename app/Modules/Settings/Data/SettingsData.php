<?php

declare(strict_types=1);

namespace App\Modules\Settings\Data;

final readonly class SettingsData
{
    /** @param  array<string, string|bool>  $values  SettingKey value => new value */
    public function __construct(public array $values) {}
}
