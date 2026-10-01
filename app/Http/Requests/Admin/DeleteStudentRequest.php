<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DeleteStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admin_password' => ['required', 'current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'admin_password.required' => 'Unesite lozinku administratorskog naloga.',
            'admin_password.current_password' => 'Uneta administratorska lozinka nije ispravna.',
        ];
    }
}
