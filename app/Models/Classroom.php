<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ClassroomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property string $teacher_id
 * @property string $name
 * @property int $grade
 * @property string $section
 * @property string $room_code
 * @property bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
#[Fillable([
    'teacher_id',
    'name',
    'grade',
    'section',
    'room_code',
    'is_active',
])]
final class Classroom extends Model
{
    /** @use HasFactory<ClassroomFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * Generate a unique 6-character room code using unambiguous characters.
     */
    public static function generateRoomCode(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $characters[random_int(0, mb_strlen($characters) - 1)];
            }
        } while (self::query()->where('room_code', $code)->exists());

        return $code;
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'teacher_id' => 'string',
            'name' => 'string',
            'grade' => 'integer',
            'section' => 'string',
            'room_code' => 'string',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * @return HasMany<User, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'classroom_id');
    }
}
