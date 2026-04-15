<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

final class BadgeSeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, description: string, icon: string, trigger_type: string}>
     */
    private const array BADGES = [
        [
            'name' => 'First Boss Defeat',
            'description' => 'Defeated your first boss in battle',
            'icon' => 'badge_first_boss',
            'trigger_type' => 'first_boss_defeat',
        ],
        [
            'name' => 'Three-Star Level',
            'description' => 'Scored 90% or above on a level',
            'icon' => 'badge_three_star',
            'trigger_type' => 'three_star_level',
        ],
        [
            'name' => 'Quarter Complete',
            'description' => 'Completed all 4 levels and the boss in a quarter',
            'icon' => 'badge_quarter_complete',
            'trigger_type' => 'quarter_complete',
        ],
        [
            'name' => 'Full World Complete',
            'description' => 'Completed all 4 quarters in one subject world',
            'icon' => 'badge_world_complete',
            'trigger_type' => 'full_world_complete',
        ],
        [
            'name' => '3-Day Streak',
            'description' => 'Played on 3 consecutive calendar days',
            'icon' => 'badge_streak',
            'trigger_type' => 'three_day_streak',
        ],
        [
            'name' => 'Screen Time Compliant',
            'description' => 'Stayed within screen time limits for 7 consecutive days',
            'icon' => 'badge_compliant',
            'trigger_type' => 'screen_time_compliant',
        ],
    ];

    public function run(): void
    {
        foreach (self::BADGES as $badge) {
            Badge::query()->firstOrCreate(['trigger_type' => $badge['trigger_type']], [
                'name' => $badge['name'],
                'description' => $badge['description'],
                'icon' => $badge['icon'],
            ]);
        }
    }
}
