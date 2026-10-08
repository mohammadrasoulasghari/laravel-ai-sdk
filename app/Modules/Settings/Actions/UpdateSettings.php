<?php

declare(strict_types=1);

namespace App\Modules\Settings\Actions;

use App\Modules\Settings\Data\SettingsData;
use App\Modules\Settings\Enums\SettingKey;
use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Queries\SettingsQuery;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\ConnectionInterface;

final readonly class UpdateSettings
{
    public function __construct(
        private ConnectionInterface $db,
        private Cache $cache,
    ) {}

    public function handle(SettingsData $data): void
    {
        $this->db->transaction(function () use ($data): void {
            foreach ($data->values as $key => $value) {
                Setting::query()->updateOrCreate(['key' => SettingKey::from($key)->value], ['value' => $value]);
            }
        });

        $this->cache->forget(SettingsQuery::CACHE_KEY);
    }
}
