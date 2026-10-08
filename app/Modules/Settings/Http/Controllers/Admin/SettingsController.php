<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Controllers\Admin;

use App\Modules\Settings\Actions\UpdateSettings;
use App\Modules\Settings\Contracts\SettingsStore;
use App\Modules\Settings\Enums\SettingKey;
use App\Modules\Settings\Enums\SettingPermission;
use App\Modules\Settings\Http\Requests\UpdateSettingsRequest;
use App\Shared\Http\Flash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class SettingsController
{
    public function edit(SettingsStore $settings): View
    {
        Gate::authorize(SettingPermission::Manage->value);

        return view('settings::admin.edit', [
            'keys' => SettingKey::cases(),
            'values' => $settings->all(),
        ]);
    }

    public function update(UpdateSettingsRequest $request, UpdateSettings $update): RedirectResponse
    {
        $update->handle($request->toData());

        return to_route('admin.settings.edit')->with(Flash::success(__('settings::settings.updated')));
    }
}
