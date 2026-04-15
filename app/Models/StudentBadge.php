<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $student_id
 * @property string $badge_id
 * @property CarbonInterface $earned_at
 */
#[Fillable([
    'student_id',
    'badge_id',
    'earned_at',
])]
final class StudentBadge extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'earned_at' => 'datetime',
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
     * @return BelongsTo<Badge, $this>
     */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
