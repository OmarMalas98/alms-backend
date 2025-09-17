<?php

namespace Database\Factories;

use App\Models\LearningObjective;
use Illuminate\Database\Eloquent\Factories\Factory;

class LearningObjectiveFactory extends Factory
{
    protected $model = LearningObjective::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence,
            'course_id' => 1,
            'level' => 'Easy',
        ];
    }
}
