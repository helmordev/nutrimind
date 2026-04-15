<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DifficultyLevel;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property string $student_id
 * @property string $level_id
 * @property DifficultyLevel $difficulty_played
 * @property float $score
 * @property int $stars
 * @property int $attempts
 * @property int $time_taken_seconds
 * @property CarbonInterface $completed_at
 * @property string $local_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
#[Fillable([
    'student_id',
    'level_id',
    'difficulty_played',
    'score',
    'stars',
    'attempts',
    'time_taken_seconds',
    'completed_at',
    'local_id',
])]
final class StudentProgress extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'difficulty_played' => DifficultyLevel::class,
            'score' => 'decimal:2',
            'stars' => 'integer',
            'attempts' => 'integer',
            'time_taken_seconds' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * @return BelongsTo<Level, $this>
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }
}
