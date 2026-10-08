<?php

declare(strict_types=1);

namespace App\Modules\Settings\Enums;

/**
 * Every runtime-editable setting. Add a case + default + rules + translations to add one.
 */
enum SettingKey: string
{
    case RegistrationOpen = 'registration_open';
    case SupportEmail = 'support_email';

    public function default(): string|bool
    {
        return match ($this) {
            self::RegistrationOpen => true,
            self::SupportEmail => 'support@example.com',
        };
    }

    /** @return list<string> */
    public function rules(): array
    {
        return match ($this) {
            self::RegistrationOpen => ['boolean'],
            self::SupportEmail => ['required', 'email', 'max:255'],
        };
    }

    public function isBoolean(): bool
    {
        return is_bool($this->default());
    }
}
