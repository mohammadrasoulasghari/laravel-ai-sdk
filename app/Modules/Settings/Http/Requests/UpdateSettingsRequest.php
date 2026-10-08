<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Requests;

use App\Modules\Settings\Data\SettingsData;
use App\Modules\Settings\Enums\SettingKey;
use App\Modules\Settings\Enums\SettingPermission;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(SettingPermission::Manage->value) ?? false;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        $rules = [];

        foreach (SettingKey::cases() as $key) {
            $rules[$key->value] = $key->rules();
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        $attributes = [];

        foreach (SettingKey::cases() as $key) {
            $attributes[$key->value] = (string) __('settings::settings.keys.'.$key->value.'.label');
        }

        return $attributes;
    }

    public function toData(): SettingsData
    {
        $values = [];

        foreach (SettingKey::cases() as $key) {
            $values[$key->value] = $key->isBoolean() ? $this->boolean($key->value) : $this->string($key->value)->toString();
        }

        return new SettingsData($values);
    }
}
