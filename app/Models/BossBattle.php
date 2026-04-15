<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property string $quarter_id
 * @property string $boss_name
 * @property int $total_hp
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class BossBattle extends Model
{
    use HasUuids;

    protected $fillable = [
        'quarter_id',
        'boss_name',
        'total_hp',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'total_hp' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Quarter, $this>
     */
    public function quarter(): BelongsTo
    {
        return $this->belongsTo(Quarter::class);
    }
}
