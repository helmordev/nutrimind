<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

final class SubjectSeeder extends Seeder
{
    /**
     * Subject definitions shared across grades.
     *
     * @var array<int, array{name: string, world_theme: string, color_hex: string}>
     */
    private const array SUBJECTS = [
        [
            'name' => 'English',
            'world_theme' => 'Library Dungeon',
            'color_hex' => '#4A90D9',
        ],
        [
            'name' => 'Science',
            'world_theme' => 'Lab Cave',
            'color_hex' => '#50C878',
        ],
        [
            'name' => 'Health+PE',
            'world_theme' => 'Sports Arena',
            'color_hex' => '#FF6B6B',
        ],
    ];

    /** @var array<int, int> */
    private const array GRADES = [5, 6];

    public function run(): void
    {
        foreach (self::GRADES as $grade) {
            foreach (self::SUBJECTS as $subject) {
                Subject::query()->firstOrCreate([
                    'name' => $subject['name'],
                    'grade' => $grade,
                ], [
                    'world_theme' => $subject['world_theme'],
                    'color_hex' => $subject['color_hex'],
                ]);
            }
        }
    }
}
