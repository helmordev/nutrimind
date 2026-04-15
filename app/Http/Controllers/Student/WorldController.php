<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Resources\WorldResource;
use App\Models\Subject;
use App\Models\User;
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
            ->with([
                'studentDifficulties' => fn ($query) => $query
                    ->where('student_id', $student->id),
                'quarters' => fn ($query) => $query
                    ->orderBy('quarter_number')
                    ->with([
                        'levels' => fn ($levelQuery) => $levelQuery->orderBy('level_number'),
                    ]),
            ])
            ->orderBy('name')
            ->get();

        return WorldResource::collection($worlds);
    }
}
