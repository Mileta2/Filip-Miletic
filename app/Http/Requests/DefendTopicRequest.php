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
        $topic = $this->route('topic');
        $professorExists = Rule::exists('users', 'id')
            ->where('role', UserRole::Professor->value)
            ->where('is_active', true)
            ->whereNull('deleted_at');

        $defenseDateRules = ['required', 'date', 'before_or_equal:today'];

        if ($topic->reserved_at) {
            $defenseDateRules[] = 'after_or_equal:'.$topic->reserved_at->toDateString();
        }

        return [
            'defended_at' => $defenseDateRules,
            'president_id' => ['required', $professorExists],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['required', 'distinct', $professorExists],
        ];
    }

    public function messages(): array
    {
        return [
            'defended_at.after_or_equal' => 'Datum odbrane ne može biti pre datuma rezervacije teme.',
        ];
    }
}
