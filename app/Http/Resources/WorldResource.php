<?php

declare(strict_types=1);

namespace App\Http\Resources;

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
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'grade' => $this->resource->grade,
            'world_theme' => $this->resource->world_theme,
            'color_hex' => $this->resource->color_hex,
            'difficulty' => $this->resource->studentDifficulties->first()?->difficulty?->value,
            'quarters' => QuarterResource::collection($this->resource->quarters),
        ];
    }
}
