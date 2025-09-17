<?php

namespace Database\Seeders;

use App\Models\Content\Content;
use App\Models\Course;
use Illuminate\Database\Seeder;

class CoursesTableSeeder extends Seeder
{
    public function run()
    {
        $content = Content::create([
            'title' => 'Introduction to Laravel',
            'content_type' => 'course',
        ]);

        $course = Course::create([
            'title' => 'Introduction to Laravel',
            'description' => 'This course provides an introduction to the Laravel framework.',
            'level_id' => 1,
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $content->id,
        ]);

        $course->admins()->attach(1);

    }
}

