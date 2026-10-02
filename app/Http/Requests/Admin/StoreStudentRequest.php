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
        $indexPrefix = trim((string) $this->input('index_number_prefix'));
        $indexSuffix = trim((string) $this->input('index_number_suffix'));
        $existingIndex = trim((string) $this->input('index_number'));

        if (($indexPrefix === '' || $indexSuffix === '') && str_contains($existingIndex, '/')) {
            [$existingPrefix, $existingSuffix] = array_pad(explode('/', $existingIndex, 2), 2, '');
            $indexPrefix = $indexPrefix ?: trim($existingPrefix);
            $indexSuffix = $indexSuffix ?: trim($existingSuffix);
        }

        $indexNumber = $indexPrefix.'/'.$indexSuffix;
        $email = mb_strtolower(trim((string) $this->input('email')));

        if ($email === '' && $this->filled(['first_name', 'last_name']) && $indexPrefix !== '' && $indexSuffix !== '') {
            $email = InstitutionalEmail::forStudent(
                $this->string('first_name')->toString(),
                $this->string('last_name')->toString(),
                $indexNumber,
            );
        }

        $this->merge([
            'email' => $email,
            'index_number_prefix' => $indexPrefix,
            'index_number_suffix' => $indexSuffix,
            'index_number' => $indexNumber,
        ]);
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
            'index_number_prefix' => ['required', 'regex:/^\d{1,8}$/'],
            'index_number_suffix' => ['required', 'regex:/^\d{1,8}$/'],
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
            'index_number_prefix.regex' => 'Prvi deo broja indeksa mora sadržati od jedne do osam cifara.',
            'index_number_suffix.regex' => 'Drugi deo broja indeksa mora sadržati od jedne do osam cifara.',
        ];
    }
}
