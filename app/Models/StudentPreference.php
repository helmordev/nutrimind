<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Language;
use App\Enums\TextSize;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property string $user_id
 * @property Language $language
 * @property int $master_volume
 * @property int $bgm_volume
 * @property int $sfx_volume
 * @property bool $tts_enabled
 * @property TextSize $text_size
 * @property bool $colorblind_mode
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
#[Fillable([
    'user_id',
    'language',
    'master_volume',
    'bgm_volume',
    'sfx_volume',
    'tts_enabled',
    'text_size',
    'colorblind_mode',
])]
final class StudentPreference extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'language' => Language::class,
            'text_size' => TextSize::class,
            'master_volume' => 'integer',
            'bgm_volume' => 'integer',
            'sfx_volume' => 'integer',
            'tts_enabled' => 'boolean',
            'colorblind_mode' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
