<?php

namespace Database\Factories;

use App\Enums\TopicStatus;
use App\Enums\TopicType;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TopicFactory extends Factory
{
    protected $model = Topic::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'description' => fake()->paragraphs(2, true),
            'type' => TopicType::Undergraduate,
            'status' => TopicStatus::Available,
            'mentor_id' => User::factory()->professor(),
        ];
    }
}
