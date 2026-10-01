<?php

namespace App\Http\Requests\Admin;

use App\Enums\StudyLevel;
use App\Support\InstitutionalEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreStudentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $email = mb_strtolower(trim((string) $this->input('email')));

        if ($email === '' && $this->filled(['first_name', 'last_name', 'index_number'])) {
            $email = InstitutionalEmail::forStudent(
                $this->string('first_name')->toString(),
                $this->string('last_name')->toString(),
                $this->string('index_number')->toString(),
            );
        }

        $this->merge(['email' => $email]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'ends_with:@'.config('app.email_domain'), 'unique:users,email'],
            'index_number' => ['required', 'string', 'max:50', 'unique:student_profiles,index_number'],
            'study_level' => ['required', Rule::enum(StudyLevel::class)],
            'study_year' => ['required', 'integer', 'min:1', Rule::when($this->input('study_level') === StudyLevel::Master->value, ['max:2'], ['max:4'])],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
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
