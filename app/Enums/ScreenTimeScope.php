<?php

declare(strict_types=1);

namespace App\Enums;

enum ScreenTimeScope: string
{
    case Global = 'global';
    case ClassScope = 'class';
    case Student = 'student';

    public function label(): string
    {
        return match ($this) {
            self::Global => 'Global',
            self::ClassScope => 'Class',
            self::Student => 'Student',
        };
    }
}
