<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

final class CreateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'lrn' => ['required', 'string', 'size:12', 'unique:student_profiles,lrn'],
            'grade' => ['required', 'integer', 'in:5,6'],
            'section' => ['required', 'string', 'max:255'],
        ];
    }
}
