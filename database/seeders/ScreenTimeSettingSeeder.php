<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ScreenTimeScope;
use App\Models\ScreenTimeSetting;
use Illuminate\Database\Seeder;

final class ScreenTimeSettingSeeder extends Seeder
{
    public function run(): void
    {
        ScreenTimeSetting::query()->firstOrCreate([
            'scope' => ScreenTimeScope::Global,
            'scope_id' => null,
        ], [
            'school_day_limit_min' => 45,
            'weekend_limit_min' => 60,
            'max_levels_school' => 2,
            'max_levels_weekend' => 3,
            'play_start_school' => '15:00',
            'play_end_school' => '20:00',
            'play_start_weekend' => '08:00',
            'play_end_weekend' => '20:00',
        ]);
    }
}
