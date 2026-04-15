<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Subject;
use Illuminate\Database\Seeder;

final class LevelSeeder extends Seeder
{
    /**
     * Level titles organized by grade, then subject name, then quarter number (1-4).
     * Each quarter has exactly 4 level titles.
     *
     * @var array<int, array<string, array<int, array<int, string>>>>
     */
    private const array LEVEL_TITLES = [
        5 => [
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
        ],
        6 => [
            'English' => [
                1 => ['Advanced Phonics', 'Context Clues', 'Critical Reading', 'Sentence Structure'],
                2 => ['Literary Devices', 'Narrative Writing', 'Persuasive Text', 'Verb Tenses'],
                3 => ['Essay Writing', 'Word Roots & Affixes', 'Public Speaking', 'Drawing Conclusions'],
                4 => ['Drama & Scripts', 'Citation & Sources', 'Argumentation', 'Cumulative Review'],
            ],
            'Science' => [
                1 => ['Scientific Method', 'Organ Systems', 'Chemical Changes', 'Biomes & Habitats'],
                2 => ['Mixtures & Solutions', 'Cells & Organisms', 'Energy Transfer', 'Climate Patterns'],
                3 => ['Gravity & Friction', 'Electricity Basics', 'Compound Machines', 'Lab Skills'],
                4 => ['Solar System', 'Conservation', 'Innovation & Tech', 'Cumulative Review'],
            ],
            'Health+PE' => [
                1 => ['Growth & Development', 'Balanced Diet Planning', 'Communicable Diseases', 'Locomotor Skills'],
                2 => ['Emergency Response', 'Emotional Wellness', 'Competitive Sports', 'Endurance Training'],
                3 => ['Peer Pressure', 'Drug Education', 'Cooperative Games', 'Injury Prevention'],
                4 => ['Public Health Issues', 'Non-communicable Diseases', 'Filipino Traditional Games', 'Cumulative PE Review'],
            ],
        ],
    ];

    public function run(): void
    {
        $subjects = Subject::with('quarters')->get();

        foreach ($subjects as $subject) {
            $titles = self::LEVEL_TITLES[$subject->grade][$subject->name] ?? [];

            foreach ($subject->quarters as $quarter) {
                $quarterTitles = $titles[$quarter->quarter_number] ?? [];

                foreach ($quarterTitles as $index => $title) {
                    $levelNumber = $index + 1;

                    Level::query()->firstOrCreate([
                        'quarter_id' => $quarter->id,
                        'level_number' => $levelNumber,
                    ], [
                        'title' => $title,
                        'matatag_competency_code' => null,
                        'matatag_competency_desc' => null,
                        'unlock_week' => $levelNumber,
                    ]);
                }
            }
        }
    }
}
