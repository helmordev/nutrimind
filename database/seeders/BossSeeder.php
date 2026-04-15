<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BossBattle;
use App\Models\Subject;
use Illuminate\Database\Seeder;

final class BossSeeder extends Seeder
{
    /**
     * Boss mapping: subject name => [quarter_number => boss_name].
     *
     * Word Warden: English Q1-Q4
     * Contaminus: Science Q1-Q4
     * Junklord: Health+PE Q1,Q3 (odd quarters)
     * Idle Rex: Health+PE Q2,Q4 (even quarters)
     *
     * @var array<string, array<int, string>>
     */
    private const array BOSS_MAP = [
        'English' => [1 => 'Word Warden', 2 => 'Word Warden', 3 => 'Word Warden', 4 => 'Word Warden'],
        'Science' => [1 => 'Contaminus', 2 => 'Contaminus', 3 => 'Contaminus', 4 => 'Contaminus'],
        'Health+PE' => [1 => 'Junklord', 2 => 'Idle Rex', 3 => 'Junklord', 4 => 'Idle Rex'],
    ];

    public function run(): void
    {
        $subjects = Subject::with('quarters')->get();

        foreach ($subjects as $subject) {
            $bossMap = self::BOSS_MAP[$subject->name] ?? [];

            foreach ($subject->quarters as $quarter) {
                $bossName = $bossMap[$quarter->quarter_number] ?? null;

                if ($bossName === null) {
                    continue;
                }

                BossBattle::query()->firstOrCreate(['quarter_id' => $quarter->id], [
                    'boss_name' => $bossName,
                    'total_hp' => 500,
                ]);
            }
        }
    }
}
