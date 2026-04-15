<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property-read string $id
 * @property string $role
 * @property string $full_name
 * @property string $username
 * @property string $password
 * @property int|null $grade
 * @property string|null $section
 * @property string|null $teacher_id
 * @property bool $is_active
 * @property bool $must_change_password
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
#[Hidden(['password'])]
final class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasUuids;
    use Notifiable;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'role' => UserRole::class,
            'full_name' => 'string',
            'username' => 'string',
            'password' => 'hashed',
            'grade' => 'integer',
            'section' => 'string',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(self::class, 'teacher_id');
    }

    /**
     * @return HasMany<User, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(self::class, 'teacher_id');
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function isTeacher(): bool
    {
        return $this->role === UserRole::Teacher;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }
}
