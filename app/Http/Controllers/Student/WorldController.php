<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Resources\WorldResource;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class WorldController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $student */
        $student = $request->user();

        $worlds = Subject::query()
            ->where('grade', $student->grade)
            ->with([ // @phpstan-ignore argument.type
                'studentDifficulties' => fn (HasMany $query): HasMany => $query
                    ->where('student_id', $student->id),
                'quarters' => fn (HasMany $query): HasMany => $query
                    ->orderBy('quarter_number')
                    ->with([ // @phpstan-ignore argument.type
                        'levels' => fn (HasMany $levelQuery): HasMany => $levelQuery->orderBy('level_number'),
                    ]),
            ])
            ->orderBy('name')
            ->get();

        return WorldResource::collection($worlds);
    }
}
