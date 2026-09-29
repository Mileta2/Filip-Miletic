<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfessorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $professor = $this->route('professor');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($professor)],
            'academic_title' => ['required', 'string', 'max:150'],
            'department' => ['nullable', 'string', 'max:255'],
            'research_area' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
