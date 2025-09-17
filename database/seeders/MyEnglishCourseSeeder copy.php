<?php

namespace Database\Seeders;

use App\Models\Components\Page;
use App\Models\Components\Question\Question;
use App\Models\Components\TextArea;
use App\Models\Components\Title;
use App\Models\Components\Video;
use App\Models\Content\Assessment;
use App\Models\Content\Lesson;
use App\Models\Course;
use App\Models\LearningObjective;
use Choice\MultiChoiceQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MyEnglishCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course = Course::factory()->create();
        $learningObjective = LearningObjective::factory()->create();
        $lesson = Lesson::factory()
            ->withPrentAndOrder($course->content->id,1)
            ->withObjective($learningObjective->id)
            ->create();
        $course->duration += $lesson->duration;
        $course->save();
        $this->lessonPagesGenerator($lesson,'simple',3);
        $this->lessonPagesGenerator($lesson,'medium',5);
        $this->lessonPagesGenerator($lesson,'more explanation',8);
        $assessment = Assessment::factory()
            ->withPrentAndOrder($course->content->id,2)
            ->withObjective($learningObjective->id)
            ->create();
        $course->duration += $assessment->duration;
        $this->addQuestions($assessment);
        $course->save();


        $course = Course::factory()->create();
        $course->duration = 23;
        $course->save();$course = Course::factory()->create();
        $course->duration = 14;
        $course->save();$course = Course::factory()->create();
        $course->duration = 51;
        $course->save();$course = Course::factory()->create();
        $course->duration = 41;
        $course->save();
    }

    public function lessonPagesGenerator($lesson, $explanationLevel, $numOfPages)
    {
        $explanationLevel = $lesson->explanationLevel($explanationLevel);
        for ($i = 1; $i <= $numOfPages; $i++) {
            $page = Page::create([
                'lesson_id' => $lesson->id,
                'explanation_level_id' => $explanationLevel->id,
                'order' => $i,
            ]);

            Title::factory()
                ->withPageAndOrder($page->id, 1)
                ->create();

            TextArea::factory()
                ->withPageAndOrder($page->id, 2)
                ->create();

            Video::factory()
                ->withPageAndOrder($page->id, 3)
                ->create();
        }
    }

    public function addQuestions($assessment){
        $question1=Question::create([
                'assessment_id' => $assessment->id,
                'order' => 1,
                'type' => 'multi-choice',
                'points'=>10
            ]
        );
        $assessmet = $question1->assessment;
        $assessmet->points += $question1->points;
        $assessmet->save();
        $multiquestion1=MultiChoiceQuestion::create([
                'question_id'=>$question1->id,
                'text'=>'_______ is my bedroom.',
            ]
        );
        $options1 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "this"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "these"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "those"
            ],
        ];
        DB::table('options')->insert($options1);
        $question1=Question::create([
                'assessment_id' => $assessment->id,
                'order' => 2,
                'type' => 'multi-choice',
                'points'=>10
            ]
        );
        $assessmet = $question1->assessment;
        $assessmet->points += $question1->points;
        $assessmet->save();
        $multiquestion1=MultiChoiceQuestion::create([
                'question_id'=>$question1->id,
                'text'=>'Look there, _______ is my teacher on the bus.',
            ]
        );
        $options1 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "this"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "those"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "that"
            ],
        ];
        DB::table('options')->insert($options1);

        $question1=Question::create([
                'assessment_id' => $assessment->id,
                'order' => 3,
                'type' => 'multi-choice',
                'points'=>10
            ]
        );
        $assessmet = $question1->assessment;
        $assessmet->points += $question1->points;
        $assessmet->save();
        $multiquestion1=MultiChoiceQuestion::create([
                'question_id'=>$question1->id,
                'text'=>'Is _______ your book over there?',
            ]
        );
        $options1 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "this"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "those"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "that"
            ],
        ];
        DB::table('options')->insert($options1);
    }

}
