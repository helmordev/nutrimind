<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property string $student_id
 * @property string $subject_id
 * @property int $quarter_number
 * @property float|null $written_work
 * @property float|null $performance_task
 * @property float|null $quarterly_assessment
 * @property float|null $final_grade
 * @property CarbonInterface|null $computed_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
#[Fillable([
    'student_id',
    'subject_id',
    'quarter_number',
    'written_work',
    'performance_task',
    'quarterly_assessment',
    'final_grade',
    'computed_at',
])]
final class GradeRecord extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'quarter_number' => 'integer',
            'written_work' => 'decimal:2',
            'performance_task' => 'decimal:2',
            'quarterly_assessment' => 'decimal:2',
            'final_grade' => 'decimal:2',
            'computed_at' => 'datetime',
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
