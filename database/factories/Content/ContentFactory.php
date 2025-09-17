<?php

namespace Database\Factories\Content;

use App\Models\Content\Content;
use Illuminate\Database\Eloquent\Factories\Factory;


class ContentFactory extends Factory
{
    protected $model = Content::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'content_type' => 'course',
        ];
    }
}
