<?php

namespace App\Http\Requests\Admin;

use App\Enums\StudyLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateStudentRequest extends FormRequest
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

        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'index_number_prefix' => $indexPrefix,
            'index_number_suffix' => $indexSuffix,
            'index_number' => $indexPrefix.'/'.$indexSuffix,
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
            'index_number_prefix' => ['required', 'regex:/^\d{1,8}$/'],
            'index_number_suffix' => ['required', 'regex:/^\d{1,8}$/'],
            'index_number' => ['required', 'string', 'max:50', Rule::unique('student_profiles')->ignore($student->studentProfile)],
            'study_level' => ['required', Rule::enum(StudyLevel::class)],
            'study_year' => ['required', 'integer', 'min:1', Rule::when($this->input('study_level') === StudyLevel::Master->value, ['max:2'], ['max:4'])],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $student = $this->route('student');
            $selectedTopic = $student?->selectedTopic;

            if ($selectedTopic && $selectedTopic->type->value !== $this->input('study_level')) {
                $validator->errors()->add(
                    'study_level',
                    'Nivo studija nije moguće promeniti dok student ima odabranu temu drugog nivoa.'
                );
            }
        });
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
