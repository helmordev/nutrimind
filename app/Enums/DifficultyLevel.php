<?php

declare(strict_types=1);

namespace App\Enums;

enum DifficultyLevel: string
{
    case Easy = 'easy';
    case Standard = 'standard';
    case Hard = 'hard';

    public function label(): string
    {
        return match ($this) {
            self::Easy => 'Easy',
            self::Standard => 'Standard',
            self::Hard => 'Hard',
        };
    }

    public function numericValue(): int
    {
        return match ($this) {
            self::Easy => 1,
            self::Standard => 2,
            self::Hard => 3,
        };
    }
}
