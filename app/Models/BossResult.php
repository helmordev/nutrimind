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
 * @property string $boss_battle_id
 * @property DifficultyLevel $difficulty_played
 * @property float $score
 * @property int $hp_dealt
 * @property CarbonInterface $completed_at
 * @property string $local_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
#[Fillable([
    'student_id',
    'boss_battle_id',
    'difficulty_played',
    'score',
    'hp_dealt',
    'completed_at',
    'local_id',
])]
final class BossResult extends Model
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
            'difficulty_played' => DifficultyLevel::class,
            'score' => 'decimal:2',
            'hp_dealt' => 'integer',
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
     * @return BelongsTo<BossBattle, $this>
     */
    public function bossBattle(): BelongsTo
    {
        return $this->belongsTo(BossBattle::class);
    }
}
