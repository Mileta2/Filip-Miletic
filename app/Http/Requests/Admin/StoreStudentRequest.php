<?php

namespace App\Http\Requests\Admin;

use App\Enums\StudyLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'index_number' => ['required', 'string', 'max:50', 'unique:student_profiles,index_number'],
            'study_level' => ['required', Rule::enum(StudyLevel::class)],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
