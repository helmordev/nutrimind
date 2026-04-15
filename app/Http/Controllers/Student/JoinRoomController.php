<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Requests\JoinRoomRequest;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class JoinRoomController
{
    public function __invoke(JoinRoomRequest $request): JsonResponse
    {
        /** @var User $student */
        $student = $request->user();

        $roomCode = mb_strtoupper((string) $request->validated('room_code')); // @phpstan-ignore cast.string

        /** @var Classroom $classroom */
        $classroom = Classroom::query()
            ->where('room_code', $roomCode)
            ->firstOrFail();

        if ($student->classroom_id === $classroom->id) {
            return response()->json([
                'message' => 'You are already in this classroom.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $student->update(['classroom_id' => $classroom->id]);

        return response()->json([
            'message' => 'Successfully joined the classroom.',
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'grade' => $classroom->grade,
                'section' => $classroom->section,
                'room_code' => $classroom->room_code,
            ],
        ]);
    }
}
