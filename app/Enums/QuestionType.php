<?php

declare(strict_types=1);

namespace App\Enums;

enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case TrueOrFalse = 'true_or_false';
    case Identification = 'identification';
    case Matching = 'matching';
    case Sequencing = 'sequencing';

    public function label(): string
    {
        return match ($this) {
            self::MultipleChoice => 'Multiple Choice',
            self::TrueOrFalse => 'True or False',
            self::Identification => 'Identification',
            self::Matching => 'Matching',
            self::Sequencing => 'Sequencing',
        };
    }
}
