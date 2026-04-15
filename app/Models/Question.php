<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property string $level_id
 * @property QuestionType $question_type
 * @property array<string, mixed> $content
 * @property array<string, mixed> $correct_answer
 * @property int $difficulty
 * @property int $order_index
 * @property bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Question extends Model
{
    use HasUuids;

    protected $fillable = [
        'level_id',
        'question_type',
        'content',
        'correct_answer',
        'difficulty',
        'order_index',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'question_type' => QuestionType::class,
            'content' => 'array',
            'correct_answer' => 'array',
            'difficulty' => 'integer',
            'order_index' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Level, $this>
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }
}
