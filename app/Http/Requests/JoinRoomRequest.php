<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Classroom;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class JoinRoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'room_code' => ['required', 'string', 'size:6'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var string $roomCode */
                $roomCode = mb_strtoupper((string) $this->input('room_code'));

                $classroom = Classroom::query()
                    ->where('room_code', $roomCode)
                    ->first();

                if (! $classroom) {
                    $validator->errors()->add('room_code', 'No classroom found with this room code.');

                    return;
                }

                if (! $classroom->is_active) {
                    $validator->errors()->add('room_code', 'This classroom is no longer active.');
                }
            },
        ];
    }
}
