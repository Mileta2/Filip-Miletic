<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfessorRequest extends FormRequest
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
        $professor = $this->route('professor');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'ends_with:@'.config('app.email_domain'), Rule::unique('users')->ignore($professor)],
            'academic_title' => ['required', 'string', 'max:150'],
            'department' => ['nullable', 'string', 'max:255'],
            'research_area' => ['nullable', 'string', 'max:2000'],
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
