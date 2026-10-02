<?php

namespace App\Http\Requests;

use App\Enums\TopicStatus;
use App\Enums\TopicType;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('topic'));
    }

    public function rules(): array
    {
        $topic = $this->route('topic');
        $professorExists = Rule::exists('users', 'id')
            ->where('role', UserRole::Professor->value)
            ->whereNull('deleted_at');

        if ($topic->status === TopicStatus::Available) {
            $professorExists->where('is_active', true);
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'course' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:10000'],
            'type' => ['required', Rule::enum(TopicType::class)],
            'mentor_id' => [
                Rule::requiredIf($this->user()->hasRole(UserRole::SuperAdmin)),
                'nullable',
                $professorExists,
            ],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
