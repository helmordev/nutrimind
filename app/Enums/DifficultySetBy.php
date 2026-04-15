<?php

declare(strict_types=1);

namespace App\Enums;

enum DifficultySetBy: string
{
    case System = 'system_default';
    case Teacher = 'teacher';

    public function label(): string
    {
        return match ($this) {
            self::System => 'System Default',
            self::Teacher => 'Teacher',
        };
    }
}
