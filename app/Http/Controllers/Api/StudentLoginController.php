<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\StudentLoginRequest;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

final class StudentLoginController
{
    public function __invoke(StudentLoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $profile = StudentProfile::where('lrn', $validated['lrn'])->first();

        if (! $profile || ! Hash::check($validated['pin'], $profile->pin)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $student = $profile->student;

        if (! $student->is_active) {
            return response()->json([
                'message' => 'This account has been deactivated.',
            ], Response::HTTP_FORBIDDEN);
        }

        $profile->update(['last_login_at' => now()]);

        $token = $student->createToken('student-api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'student' => [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'grade' => $student->grade,
                'section' => $student->section,
                'must_change_password' => $student->must_change_password,
            ],
        ]);
    }
}
