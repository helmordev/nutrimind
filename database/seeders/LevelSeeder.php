<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Quarter;
use App\Models\Subject;
use Illuminate\Database\Seeder;

final class LevelSeeder extends Seeder
{
    /**
     * Level titles organized by subject name, then quarter number (1-4), then level number (1-4).
     *
     * @var array<string, array<int, array<int, string>>>
     */
    private const array LEVEL_TITLES = [
        'English' => [
            1 => ['Phonics & Letters', 'Vocabulary Building', 'Reading Comprehension', 'Grammar Basics'],
            2 => ['Figurative Language', 'Story Elements', 'Main Idea & Details', 'Parts of Speech'],
            3 => ['Writing Sentences', 'Spelling Patterns', 'Oral Presentation', 'Inferencing Skills'],
            4 => ['Poetry & Creative Text', 'Research Skills', 'Debate & Opinion', 'Cumulative Review'],
        ],
        'Science' => [
            1 => ['Intro to Science', 'Human Body Systems', 'States of Matter', 'Ecosystems'],
            2 => ['Properties of Matter', 'Living vs Non-living', 'Food Chains', 'Weather & Climate'],
            3 => ['Force & Motion', 'Energy Forms', 'Simple Machines', 'Science Experiments'],
            4 => ['Earth & Space', 'Environmental Care', 'Tech in Science', 'Cumulative Review'],
        ],
        'Health+PE' => [
            1 => ['Personal Hygiene', 'Nutrition & Food Groups', 'Disease Prevention', 'Basic Movement Skills'],
            2 => ['First Aid Basics', 'Mental Health', 'Sports & Games', 'Fitness Exercises'],
            3 => ['Healthy Habits', 'Substance Awareness', 'Team Sports', 'PE Safety Rules'],
            4 => ['Community Health', 'Lifestyle Diseases', 'Indigenous Games', 'Cumulative PE Review'],
        ],
    ];

    public function run(): void
    {
        $subjects = Subject::with('quarters')->get();

        foreach ($subjects as $subject) {
            $titles = self::LEVEL_TITLES[$subject->name] ?? [];

            foreach ($subject->quarters as $quarter) {
                $quarterTitles = $titles[$quarter->quarter_number] ?? [];

                foreach ($quarterTitles as $index => $title) {
                    $levelNumber = $index + 1;

                    Level::firstOrCreate(
                        [
                            'quarter_id' => $quarter->id,
                            'level_number' => $levelNumber,
                        ],
                        [
                            'title' => $title,
                            'matatag_competency_code' => null,
                            'matatag_competency_desc' => null,
                            'unlock_week' => $levelNumber,
                        ],
                    );
                }
            }
        }
    }
}
