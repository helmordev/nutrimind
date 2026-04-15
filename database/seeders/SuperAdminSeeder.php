<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        /** @var string $adminPassword */
        $adminPassword = config('app.admin_initial_password', 'password');

        User::query()->firstOrCreate(['username' => 'registrar'], [
            'role' => UserRole::SuperAdmin,
            'full_name' => 'System Administrator',
            'password' => Hash::make($adminPassword),
            'grade' => null,
            'section' => null,
            'teacher_id' => null,
            'is_active' => true,
            'must_change_password' => false,
        ]);
    }
}
