<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreProfessorRequest extends FormRequest
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
            'academic_title' => ['required', 'string', 'max:150'],
            'department' => ['nullable', 'string', 'max:255'],
            'research_area' => ['nullable', 'string', 'max:2000'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
