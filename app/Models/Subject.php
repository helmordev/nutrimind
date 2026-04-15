<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property-read string $id
 * @property string $name
 * @property string $world_theme
 * @property string $color_hex
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Subject extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'world_theme',
        'color_hex',
    ];

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
}
