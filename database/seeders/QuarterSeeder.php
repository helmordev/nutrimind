<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Quarter;
use App\Models\Subject;
use Illuminate\Database\Seeder;

final class QuarterSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = Subject::all();

        foreach ($subjects as $subject) {
            for ($q = 1; $q <= 4; $q++) {
                Quarter::firstOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'quarter_number' => $q,
                    ],
                    [
                        'current_unlock_week' => 0,
                        'is_globally_unlocked' => false,
                    ],
                );
            }
        }
    }
}
