<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class WorldResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Subject $subject */
        $subject = $this->resource;

        return [
            'id' => $subject->id,
            'name' => $subject->name,
            'grade' => $subject->grade,
            'world_theme' => $subject->world_theme,
            'color_hex' => $subject->color_hex,
            'difficulty' => $subject->studentDifficulties->first()?->difficulty?->value,
            'quarters' => QuarterResource::collection($subject->quarters),
        ];
    }
}
