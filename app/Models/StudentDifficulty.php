<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DifficultyLevel;
use App\Enums\DifficultySetBy;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property string $student_id
 * @property string $subject_id
 * @property DifficultyLevel $difficulty
 * @property DifficultySetBy $set_by
 * @property CarbonInterface|null $updated_at_by_teacher
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class StudentDifficulty extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id',
        'subject_id',
        'difficulty',
        'set_by',
        'updated_at_by_teacher',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'difficulty' => DifficultyLevel::class,
            'set_by' => DifficultySetBy::class,
            'updated_at_by_teacher' => 'datetime',
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
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
