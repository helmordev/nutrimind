<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property string $student_id
 * @property string $log_date
 * @property int $total_minutes
 * @property int $levels_played
 * @property CarbonInterface|null $last_active_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class ScreenTimeLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id',
        'log_date',
        'total_minutes',
        'levels_played',
        'last_active_at',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'log_date' => 'date',
            'total_minutes' => 'integer',
            'levels_played' => 'integer',
            'last_active_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
