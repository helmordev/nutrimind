<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property-read string $id
 * @property string $name
 * @property int $grade
 * @property string $world_theme
 * @property string $color_hex
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
#[Fillable([
    'name',
    'grade',
    'world_theme',
    'color_hex',
])]
final class Subject extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    use HasUuids;

    /**
     * @return HasMany<Quarter, $this>
     */
    public function quarters(): HasMany
    {
        return $this->hasMany(Quarter::class);
    }

    /**
     * @return HasManyThrough<Level, Quarter, $this>
     */
    public function levels(): HasManyThrough
    {
        return $this->hasManyThrough(Level::class, Quarter::class);
    }

    /**
     * @return HasMany<StudentDifficulty, $this>
     */
    public function studentDifficulties(): HasMany
    {
        return $this->hasMany(StudentDifficulty::class);
    }
}
