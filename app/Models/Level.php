<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property string $quarter_id
 * @property int $level_number
 * @property string $title
 * @property string|null $matatag_competency_code
 * @property string|null $matatag_competency_desc
 * @property int $unlock_week
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
#[Fillable([
    'quarter_id',
    'level_number',
    'title',
    'matatag_competency_code',
    'matatag_competency_desc',
    'unlock_week',
])]
final class Level extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'level_number' => 'integer',
            'unlock_week' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Quarter, $this>
     */
    public function quarter(): BelongsTo
    {
        return $this->belongsTo(Quarter::class);
    }

    /**
     * @return HasMany<Question, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
