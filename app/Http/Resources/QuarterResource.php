<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class QuarterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'quarter_number' => $this->resource->quarter_number,
            'current_unlock_week' => $this->resource->current_unlock_week,
            'is_globally_unlocked' => $this->resource->is_globally_unlocked,
            'levels' => $this->resource->levels->map(fn ($level): array => [
                'id' => $level->id,
                'level_number' => $level->level_number,
                'title' => $level->title,
                'unlock_week' => $level->unlock_week,
                'is_unlocked' => $this->resource->is_globally_unlocked
                    || $this->resource->current_unlock_week >= $level->unlock_week,
            ])->values()->all(),
        ];
    }
}
