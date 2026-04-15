<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Level;
use App\Models\Quarter;
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
        /** @var Quarter $quarter */
        $quarter = $this->resource;

        return [
            'id' => $quarter->id,
            'quarter_number' => $quarter->quarter_number,
            'current_unlock_week' => $quarter->current_unlock_week,
            'is_globally_unlocked' => $quarter->is_globally_unlocked,
            'levels' => $quarter->levels->map(fn (Level $level): array => [
                'id' => $level->id,
                'level_number' => $level->level_number,
                'title' => $level->title,
                'unlock_week' => $level->unlock_week,
                'is_unlocked' => $quarter->is_globally_unlocked
                    || $quarter->current_unlock_week >= $level->unlock_week,
            ])->values()->all(),
        ];
    }
}
