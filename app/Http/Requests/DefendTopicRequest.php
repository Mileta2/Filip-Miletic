<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DefendTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('defend', $this->route('topic'));
    }

    public function rules(): array
    {
        $professorExists = Rule::exists('users', 'id')
            ->where('role', UserRole::Professor->value)
            ->where('is_active', true);

        return [
            'defended_at' => ['required', 'date', 'before_or_equal:today'],
            'president_id' => ['required', $professorExists],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['required', 'distinct', $professorExists],
        ];
    }
}
