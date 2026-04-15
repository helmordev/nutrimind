<?php

declare(strict_types=1);

namespace App\Enums;

enum Language: string
{
    case English = 'en';
    case Filipino = 'fil';

    public function label(): string
    {
        return match ($this) {
            self::English => 'English',
            self::Filipino => 'Filipino',
        };
    }
}
