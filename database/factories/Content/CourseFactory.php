<?php

namespace Database\Factories\Content;

use App\Models\Content\Content;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition()
    {
        $title = $this->faker->sentence;

        return [
            'title' => $title,
            'description' => $this->faker->paragraph,
            'level_id' => 1,
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Course $course) {
            $title = $course->title; // Get the title from the course instance
            $content = Content::factory()->create([
                'title' => $title,
                'content_type' => 'course',
            ]);
            $course->content_id = $content->id; // Set the content_id to the created content's i
            $course->admins()->attach(1);
        });
    }
}

