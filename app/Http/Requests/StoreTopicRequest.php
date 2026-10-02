<?php

namespace App\Http\Requests;

use App\Enums\TopicType;
use App\Enums\UserRole;
use App\Models\Topic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Topic::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'course' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:10000'],
            'type' => ['required', Rule::enum(TopicType::class)],
            'mentor_id' => [
                Rule::requiredIf($this->user()->hasRole(UserRole::SuperAdmin)),
                'nullable',
                Rule::exists('users', 'id')
                    ->where('role', UserRole::Professor->value)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
