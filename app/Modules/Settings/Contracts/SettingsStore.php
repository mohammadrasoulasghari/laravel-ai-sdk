<?php

declare(strict_types=1);

namespace App\Modules\Settings\Contracts;

use App\Modules\Settings\Enums\SettingKey;

/** Typed, cached read access to runtime settings for any module. */
interface SettingsStore
{
    public function string(SettingKey $key): string;

    public function boolean(SettingKey $key): bool;

    /** @return array<string, string|bool> key => value for every SettingKey */
    public function all(): array;
}
