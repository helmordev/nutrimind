<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property string $student_id
 * @property string $subject_id
 * @property string $current_difficulty
 * @property string $suggested_difficulty
 * @property string $reason
 * @property float $rolling_avg
 * @property bool $is_reviewed
 * @property CarbonInterface|null $reviewed_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
#[Fillable([
    'student_id',
    'subject_id',
    'current_difficulty',
    'suggested_difficulty',
    'reason',
    'rolling_avg',
    'is_reviewed',
    'reviewed_at',
])]
final class DifficultyAdvisory extends Model
{
    use HasFactory;
    use HasFactory;
    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'rolling_avg' => 'decimal:2',
            'is_reviewed' => 'boolean',
            'reviewed_at' => 'datetime',
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
