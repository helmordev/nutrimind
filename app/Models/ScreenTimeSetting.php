<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ScreenTimeScope;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property ScreenTimeScope $scope
 * @property string|null $scope_id
 * @property int $school_day_limit_min
 * @property int $weekend_limit_min
 * @property int $max_levels_school
 * @property int $max_levels_weekend
 * @property string $play_start_school
 * @property string $play_end_school
 * @property string $play_start_weekend
 * @property string $play_end_weekend
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
#[Fillable([
    'scope',
    'scope_id',
    'school_day_limit_min',
    'weekend_limit_min',
    'max_levels_school',
    'max_levels_weekend',
    'play_start_school',
    'play_end_school',
    'play_start_weekend',
    'play_end_weekend',
])]
final class ScreenTimeSetting extends Model
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
            'scope' => ScreenTimeScope::class,
            'school_day_limit_min' => 'integer',
            'weekend_limit_min' => 'integer',
            'max_levels_school' => 'integer',
            'max_levels_weekend' => 'integer',
        ];
    }
}
