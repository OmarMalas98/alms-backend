<?php

namespace Database\Seeders;

use App\Models\BlankAnswer;
use App\Models\BlankQuestion;
use App\Models\Components\Component;
use App\Models\Components\Page;
use App\Models\Components\Question\MultiChoice\MultiChoiceQuestion;
use App\Models\Components\Question\MultiChoice\Option;
use App\Models\Components\Question\Question;
use App\Models\Components\TextArea;
use App\Models\Components\Title;
use App\Models\Course;
use App\Models\CrossQuestion;
use App\Models\LearningObjective;
use App\Models\ReorderingQuestion;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class LearningObjectiveSeeder extends Seeder
{
    public function run()
    {
        $course = Course::create(['title'=>'Foundations of English', 'description'=>'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s','level_id'=>1,'creator_id' => 2]);
        $course->admins()->attach(2);
        Zone::create(['title'=>'Lesson one', 'description'=>'Lorem Ipsum is simply dummy text','course_id'=>1,'level'=>1]);
        Zone::create(['title'=>'Lesson two', 'description'=>'Lorem Ipsum is simply dummy text','course_id'=>1,'level'=>2]);
        Zone::create(['title'=>'Lesson three', 'description'=>'Lorem Ipsum is simply dummy text','course_id'=>1,'level'=>2]);
        Zone::create(['title'=>'Lesson four', 'description'=>'Lorem Ipsum is simply dummy text','course_id'=>1,'level'=>3]);


        $objectivesData = [
            ['id' => 1, 'name' => 'Teach student how to spell numbers from 0 to 9 in english like 0 -> zero','zone_id' => 1],
            ['id' => 2, 'name' => 'Teach student how to spell numbers are multiples of ten in english like 20 -> twenty','zone_id' => 1],
            ['id' => 3, 'name' => 'Objective 3','zone_id' => 1],
            ['id' => 4, 'name' => 'Objective 4','zone_id' => 2],
            ['id' => 5, 'name' => 'Objective 5','zone_id' => 2],
            ['id' => 6, 'name' => 'Objective 6','zone_id' => 3],
            ['id' => 7, 'name' => 'Objective 7','zone_id' => 3],
            ['id' => 8, 'name' => 'Objective 8','zone_id' => 4],
        ];

        foreach ($objectivesData as $objectiveData) {
            $objective = LearningObjective::create($objectiveData);
        }

        // Set up objective relationships
        $relationships = [
            ['parent_id' => 1, 'child_id' => 2],
            ['parent_id' => 2, 'child_id' => 3],
            ['parent_id' => 2, 'child_id' => 4],
            ['parent_id' => 3, 'child_id' => 6],
            ['parent_id' => 4, 'child_id' => 5],
            ['parent_id' => 5, 'child_id' => 8],
            ['parent_id' => 6, 'child_id' => 7],
            ['parent_id' => 7, 'child_id' => 8],
            ];

        foreach ($relationships as $relationship) {
            $parentObjective = LearningObjective::find($relationship['parent_id']);
            $childObjective = LearningObjective::find($relationship['child_id']);

            $childObjective->parents()->attach($parentObjective);
        }

        $pages = [
            ['learning_objective_id'=>1 , 'explanation_level'=> 1 , 'order'=>'1' , 'is_question' => 0],
            ['learning_objective_id'=>1 , 'explanation_level'=> 2 , 'order'=>'2' , 'is_question' => 0],
            ['learning_objective_id'=>1 , 'explanation_level'=> 1 , 'order'=>'3' , 'is_question' => 1],
            ['learning_objective_id'=>1 , 'explanation_level'=> 1 , 'order'=>'4' , 'is_question' => 1],
            ['learning_objective_id'=>2 , 'explanation_level'=> 1 , 'order'=>'5' , 'is_question' => 0],
            ['learning_objective_id'=>3 , 'explanation_level'=> 1 , 'order'=>'7' , 'is_question' => 0],
            ['learning_objective_id'=>4 , 'explanation_level'=> 1 , 'order'=>'1' , 'is_question' => 0],
            ['learning_objective_id'=>4 , 'explanation_level'=> 1 , 'order'=>'2' , 'is_question' => 1],
            ['learning_objective_id'=>5 , 'explanation_level'=> 1 , 'order'=>'3' , 'is_question' => 0],
            ['learning_objective_id'=>2 , 'explanation_level'=> 1 , 'order'=>'6' , 'is_question' => 1],
            ['learning_objective_id'=>3 , 'explanation_level'=> 1 , 'order'=>'8' , 'is_question' => 1],
            ['learning_objective_id'=>5 , 'explanation_level'=> 1 , 'order'=>'6' , 'is_question' => 1],
            ['learning_objective_id'=>6 , 'explanation_level'=> 1 , 'order'=>'1' , 'is_question' => 0],
            ['learning_objective_id'=>7 , 'explanation_level'=> 1 , 'order'=>'2' , 'is_question' => 0],
            ['learning_objective_id'=>8 , 'explanation_level'=> 1 , 'order'=>'1' , 'is_question' => 0],
        ];

        foreach ($pages as $page){
            Page::create($page);
        }

        $titleComponents =[
          ['page_id'=>1 , 'order'=> 1 , 'type'=> 'title' ],
          ['page_id'=>2 , 'order'=> 1 , 'type'=> 'title' ],
          ['page_id'=>5 , 'order'=> 1 , 'type'=> 'title' ],
          ['page_id'=>6 , 'order'=> 1 , 'type'=> 'title' ],
          ['page_id'=>7 , 'order'=> 1 , 'type'=> 'title' ],
          ['page_id'=>9 , 'order'=> 1 , 'type'=> 'title' ],
          ['page_id'=>13 , 'order'=> 1 , 'type'=> 'title' ],
          ['page_id'=>14 , 'order'=> 1 , 'type'=> 'title' ],
          ['page_id'=>15 , 'order'=> 1 , 'type'=> 'title' ],
        ];

        $textComponents =[
            ['page_id'=>1 , 'order'=> 2 , 'type'=> 'textarea' ],
            ['page_id'=>2 , 'order'=> 2 , 'type'=> 'textarea' ],
            ['page_id'=>5 , 'order'=> 2 , 'type'=> 'textarea' ],
            ['page_id'=>6 , 'order'=> 2 , 'type'=> 'textarea' ],
            ['page_id'=>7 , 'order'=> 2 , 'type'=> 'textarea' ],
            ['page_id'=>9 , 'order'=> 2 , 'type'=> 'textarea' ],
            ['page_id'=>13 , 'order'=> 2 , 'type'=> 'textarea' ],
            ['page_id'=>14 , 'order'=> 2 , 'type'=> 'textarea' ],
            ['page_id'=>15 , 'order'=> 2 , 'type'=> 'textarea' ],
        ];

        $counter = 0;
        foreach ($titleComponents as $title){
            $component = Component::create($title);
            $counter = $counter + 1;
            Title::create(['component_id'=>$component->id , 'body'=> "This is a title {$counter} ! "]);
        }

        $counter = 0;
        foreach ($textComponents as $text){
            $component = Component::create($text);
            $counter = $counter + 1;
            TextArea::create(['component_id'=>$component->id , 'body'=> "This is a text area {$counter} ! "]);
        }

        $mcComponent = Component::create(['page_id'=>3 , 'order'=> 1 , 'type'=> 'question' ]);
        $question = Question::create(['type'=>'multi-choice' , 'component_id'=>$mcComponent->id ]);
        $multiChoiceQuestion = MultiChoiceQuestion::create(['question_id'=>$question->id , 'text'=>'5 + 2 = ?']);

        $options = [
          ['multi_choice_question_id'=>$multiChoiceQuestion->id , 'text'=> '10' , 'is_correct'=>0 ],
          ['multi_choice_question_id'=>$multiChoiceQuestion->id , 'text'=> '7' , 'is_correct'=>1 ],
          ['multi_choice_question_id'=>$multiChoiceQuestion->id , 'text'=> '3' , 'is_correct'=>0 ],
          ['multi_choice_question_id'=>$multiChoiceQuestion->id , 'text'=> '52' , 'is_correct'=>0 ],
        ];

        foreach ($options as $option){
            Option::create($option);
        }

        $bComponent = Component::create(['page_id'=>4 , 'order'=> 1 , 'type'=> 'question' ]);
        $question = Question::create(['type'=>'blank-question' , 'component_id'=>$bComponent->id ]);
        $blankQuestion = BlankQuestion::create(['question_id'=>$question->id , 'text'=>'3 + 6 = ____ ']);

        BlankAnswer::create([
            'question_id' => $blankQuestion->id,
            'blank_number' => 1,
            'answer_text' => strtolower('9'),
        ]);

        $mcComponent = Component::create(['page_id'=>8 , 'order'=> 1 , 'type'=> 'question' ]);
        $question = Question::create(['type'=>'multi-choice' , 'component_id'=>$mcComponent->id ]);
        $multiChoiceQuestion = MultiChoiceQuestion::create(['question_id'=>$question->id , 'text'=>'3 * 9 = ?']);

        $options = [
            ['multi_choice_question_id'=>$multiChoiceQuestion->id , 'text'=> '12' , 'is_correct'=>0 ],
            ['multi_choice_question_id'=>$multiChoiceQuestion->id , 'text'=> '29' , 'is_correct'=>0 ],
            ['multi_choice_question_id'=>$multiChoiceQuestion->id , 'text'=> '18' , 'is_correct'=>1 ],
            ['multi_choice_question_id'=>$multiChoiceQuestion->id , 'text'=> '3' , 'is_correct'=>0 ],
        ];

        foreach ($options as $option){
            Option::create($option);
        }

        $bComponent = Component::create(['page_id'=>10 , 'order'=> 1 , 'type'=> 'question' ]);
        $question = Question::create(['type'=>'blank-question' , 'component_id'=>$bComponent->id ]);
        $blankQuestion = BlankQuestion::create(['question_id'=>$question->id , 'text'=>'5 + 1 = ____  & 3 + 2 = ____']);

        BlankAnswer::create([
            'question_id' => $blankQuestion->id,
            'blank_number' => 1,
            'answer_text' => strtolower('6'),
        ]);

        BlankAnswer::create([
            'question_id' => $blankQuestion->id,
            'blank_number' => 2,
            'answer_text' => strtolower('5'),
        ]);

        $crossComponent = Component::create(['page_id'=>11 , 'order'=> 1 , 'type'=> 'question' ]);
        $question = Question::create(['type'=>'cross-question' , 'component_id'=>$crossComponent->id ]);
        $crossQuestion = CrossQuestion::create(['question_id'=>$question->id , 'text'=>'Match the countries with their capitals.']);

        $left = [
            ['text'=> 'United States', 'right_option_id'=> 1],
            ['text'=> 'Canada', 'right_option_id'=> 2],
            ['text'=> 'United Kingdom', 'right_option_id'=> 3],
        ];

        $right=[
          'Washington, D.C.',
            'Ottawa',
            'London',
            'Damascus'
        ];

        foreach ($right as $rightOptionData) {
            $crossQuestion->rightOptions()->create([
                'text' => $rightOptionData,
                "cross_question_id" => $crossQuestion->id
            ]);
        }

        foreach ($left as $leftOptionData) {
            $data = $crossQuestion->rightOptions[$leftOptionData['right_option_id']-1];

            $crossQuestion->leftOptions()->create([
                'text' => $leftOptionData['text'],
                'right_option_id' => $data->id,
            ]);
        }

        $orderComponent = Component::create(['page_id'=>12 , 'order'=> 1 , 'type'=> 'question' ]);
        $question = Question::create(['type'=>'reorder-question' , 'component_id'=>$orderComponent->id ]);
        $orderQuestion = ReorderingQuestion::create(['question_id'=>$question->id , 'text'=>'Rearrange the following steps to form a correct sentence']);

        $orders=[
            ['order'=> 1, 'text'=>"he"],
            ['order'=> 3, 'text'=>"a"],
            ['order'=> 4, 'text'=>"man"],
            ['order'=> 2, 'text'=>"is"],
        ];

        foreach ($orders as $order){
            $orderQuestion->items()->create([
                'text' => $order['text'],
                'order' => $order['order'],
            ]);
        }

        $course = Course::create(['title'=>'foundations of mathematics', 'description'=>'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s','level_id'=>1,'creator_id' => 2]);
        $course->admins()->attach(2);
        $course = Course::create(['title'=>'foundations of project management', 'description'=>'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s','level_id'=>2,'creator_id' => 2]);
        $course->admins()->attach(2);
        $course = Course::create(['title'=>'Foundations of physics', 'description'=>'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s','level_id'=>1,'creator_id' => 2]);
        $course->admins()->attach(2);

        Zone::create(['title'=>'Lesson one course 2', 'description'=>'Lorem Ipsum is simply dummy text','course_id'=>2,'level'=>1]);
        Zone::create(['title'=>'Lesson two course 2', 'description'=>'Lorem Ipsum is simply dummy text','course_id'=>2,'level'=>2]);
        Zone::create(['title'=>'Lesson three course 2', 'description'=>'Lorem Ipsum is simply dummy text','course_id'=>2,'level'=>2]);

        Zone::create(['title'=>'Lesson one course 3', 'description'=>'Lorem Ipsum is simply dummy text','course_id'=>3,'level'=>1]);
        Zone::create(['title'=>'Lesson two course 3', 'description'=>'Lorem Ipsum is simply dummy text','course_id'=>3,'level'=>2]);
    }
}

