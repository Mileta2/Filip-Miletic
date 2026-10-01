<?php

namespace App\Http\Requests\Admin;

use App\Enums\StudyLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'ends_with:@'.config('app.email_domain'), Rule::unique('users')->ignore($student)],
            'index_number' => ['required', 'string', 'max:50', Rule::unique('student_profiles')->ignore($student->studentProfile)],
            'study_level' => ['required', Rule::enum(StudyLevel::class)],
            'study_year' => ['required', 'integer', 'min:1', Rule::when($this->input('study_level') === StudyLevel::Master->value, ['max:2'], ['max:4'])],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.ends_with' => 'Email adresa mora pripadati domenu @'.config('app.email_domain').'.',
        ];
    }
}
