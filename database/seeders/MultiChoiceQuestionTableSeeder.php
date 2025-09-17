<?php

namespace Database\Seeders;

use App\Models\Components\Question\Question;
use Choice\MultiChoiceQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MultiChoiceQuestionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $question1=Question::create([
                'assessment_id' => 1,
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
            'text'=>'who is the goat?',
            ]
        );
        $options1 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "KAKA"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "Messi"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "Neymar"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "LAKAKA"
            ]
        ];

        DB::table('options')->insert($options1);

        $question2=Question::create([
                'assessment_id' => 1,
                'order' => 2,
                'type' => 'multi-choice',
                'points'=>10
            ]
        );
        $assessmet = $question2->assessment;
        $assessmet->points += $question2->points;
        $assessmet->save();

        $multiquestion2=MultiChoiceQuestion::create([
                'question_id'=>$question2->id,
                'text'=>'who is the scrum master?',
            ]
        );
        $options2 = [
            [
                "multi_choice_question_id" => $multiquestion2->id,
                "is_correct" => true,
                "text" => "Mahmoud"
            ],
            [
                "multi_choice_question_id" => $multiquestion2->id,
                "is_correct" => false,
                "text" => "Omar"
            ],
            [
                "multi_choice_question_id" => $multiquestion2->id,
                "is_correct" => false,
                "text" => "Abd"
            ],
            [
                "multi_choice_question_id" => $multiquestion2->id,
                "is_correct" => false,
                "text" => "Kareem"
            ]
        ];

        DB::table('options')->insert($options2);

        $question3=Question::create([
                'assessment_id' => 1,
                'order' => 3,
                'type' => 'multi-choice',
                'points'=>10
            ]
        );
        $assessmet = $question3->assessment;
        $assessmet->points += $question3->points;
        $assessmet->save();

        $multiquestion3=MultiChoiceQuestion::create([
                'question_id'=>$question3->id,
                'text'=>'who is THE REDHEAD?',
            ]
        );
        $options3 = [
            [
                "multi_choice_question_id" => $multiquestion3->id,
                "is_correct" => false,
                "text" => "Kareem"
            ],
            [
                "multi_choice_question_id" => $multiquestion3->id,
                "is_correct" => false,
                "text" => "Mahmoud"
            ],
            [
                "multi_choice_question_id" => $multiquestion3->id,
                "is_correct" => false,
                "text" => "Abd"
            ],
            [
                "multi_choice_question_id" => $multiquestion3->id,
                "is_correct" => true,
                "text" => "Omar"
            ]
        ];

        DB::table('options')->insert($options3);

        $question4=Question::create([
                'assessment_id' => 1,
                'order' => 4,
                'type' => 'multi-choice',
                'points'=>10
            ]
        );
        $assessmet = $question4->assessment;
        $assessmet->points += $question4->points;
        $assessmet->save();

        $multiquestion4=MultiChoiceQuestion::create([
                'question_id'=>$question4->id,
                'text'=>'who is THE NIGGA?',
            ]
        );
        $options4 = [
            [
                "multi_choice_question_id" => $multiquestion4->id,
                "is_correct" => false,
                "text" => "Mahmoud"
            ],
            [
                "multi_choice_question_id" => $multiquestion4->id,
                "is_correct" => false,
                "text" => "Omar"
            ],
            [
                "multi_choice_question_id" => $multiquestion4->id,
                "is_correct" => true,
                "text" => "Abd"
            ],
            [
                "multi_choice_question_id" => $multiquestion4->id,
                "is_correct" => false,
                "text" => "Kareem"
            ]
        ];

        DB::table('options')->insert($options4);

        $question5=Question::create([
                'assessment_id' => 1,
                'order' => 5,
                'type' => 'multi-choice',
                'points'=>10
            ]
        );
        $assessmet = $question5->assessment;
        $assessmet->points += $question5->points;
        $assessmet->save();

        $multiquestion5=MultiChoiceQuestion::create([
                'question_id'=>$question5->id,
                'text'=>'who does THE BARBES?',
            ]
        );
        $options5 = [
            [
                "multi_choice_question_id" => $multiquestion5->id,
                "is_correct" => true,
                "text" => "Mahmoud"
            ],
            [
                "multi_choice_question_id" => $multiquestion5->id,
                "is_correct" => false,
                "text" => "Omar"
            ],
            [
                "multi_choice_question_id" => $multiquestion5->id,
                "is_correct" => false,
                "text" => "Abd"
            ],
            [
                "multi_choice_question_id" => $multiquestion5->id,
                "is_correct" => false,
                "text" => "Kareem"
            ]
        ];

        DB::table('options')->insert($options5);

    }
}
