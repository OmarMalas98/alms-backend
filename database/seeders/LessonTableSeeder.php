<?php

namespace Database\Seeders;

use App\Models\Content\Content;
use App\Models\Content\Lesson;
use App\Models\ExplanationLevel;
use Illuminate\Database\Seeder;

class LessonTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $content = Content::create([
            'title' => "Lesson1",
            'content_type' => 'lesson',
            'parent_id' => 1,
            'order'=> 1,

        ]);

        $lesson = Lesson::create([
            'title' => "Lesson1",
            'description' => "bla bla bla",
            'duration'=> 5,
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $content->id,
            'learning_objective_id'=>1
            ]);

        $levels = ['simple', 'medium', 'more explanation'];
        foreach ($levels as $level) {
            ExplanationLevel::create([
                'lesson_id' => $lesson->id,
                'level' => $level
            ]);
        }

        $content2 = Content::create([
            'title' => "Lesson2",
            'content_type' => 'lesson',
            'parent_id' => 3,
            'order'=> 1,

        ]);

        $lesson2 = Lesson::create([
            'title' => "Lesson2",
            'description' => "bla bla bla",
            'duration'=> 3,
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $content2->id,
            'learning_objective_id'=>2
        ]);

        $levels = ['simple', 'medium', 'more explanation'];
        foreach ($levels as $level) {
            ExplanationLevel::create([
                'lesson_id' => $lesson2->id,
                'level' => $level
            ]);
        }

        $content3 = Content::create([
            'title' => "Lesson3",
            'content_type' => 'lesson',
            'parent_id' => 2,
            'order'=> 3,

        ]);

        $lesson = Lesson::create([
            'title' => "Lesson3",
            'description' => "bla bla bla",
            'duration'=> 14,
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $content3->id,
            'learning_objective_id'=>3
        ]);

        $levels = ['simple', 'medium', 'more explanation'];
        foreach ($levels as $level) {
            ExplanationLevel::create([
                'lesson_id' => $lesson->id,
                'level' => $level
            ]);
        }

        $content4 = Content::create([
            'title' => "Lesson4",
            'content_type' => 'lesson',
            'parent_id' => 1,
            'order'=> 2,

        ]);

        $lesson = Lesson::create([
            'title' => "Lesson4",
            'description' => "bla bla bla",
            'duration'=> 2,
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $content4->id,
            'learning_objective_id'=>4
        ]);

        $levels = ['simple', 'medium', 'more explanation'];
        foreach ($levels as $level) {
            ExplanationLevel::create([
                'lesson_id' => $lesson->id,
                'level' => $level
            ]);
        }


    }
}
