<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Teacher = 'teacher';
    case SuperAdmin = 'super_admin';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Student',
            self::Teacher => 'Teacher',
            self::SuperAdmin => 'Super Admin',
        };
    }
}
