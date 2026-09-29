<?php

namespace App\Http\Requests\Admin;

use App\Enums\StudyLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($student)],
            'index_number' => ['required', 'string', 'max:50', Rule::unique('student_profiles')->ignore($student->studentProfile)],
            'study_level' => ['required', Rule::enum(StudyLevel::class)],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
