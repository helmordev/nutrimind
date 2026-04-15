<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property string $name
 * @property string $description
 * @property string $icon
 * @property string $trigger_type
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Badge extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'trigger_type',
    ];
}
