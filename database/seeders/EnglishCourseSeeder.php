<?php

namespace Database\Seeders;

use App\Http\Controllers\ContentControllers\ContentController;
use App\Models\Components\Component;
use App\Models\Components\Page;
use App\Models\Components\Question\Question;
use App\Models\Components\TextArea;
use App\Models\Components\Title;
use App\Models\Components\Video;
use App\Models\Content\Assessment;
use App\Models\Content\Content;
use App\Models\Content\Lesson;
use App\Models\Course;
use App\Models\ExplanationLevel;
use App\Models\LearningObjective;
use Choice\MultiChoiceQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnglishCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courseContent = Content::create([
            'title' => 'English Grammar',
            'content_type' => 'course',
        ]);

        $course = Course::create([
            'title' => 'English Grammar',
            'description' => 'The course "Introduction to English Grammar" provides a comprehensive foundation for understanding and effectively using the fundamental elements of English grammar. Designed for individuals seeking to enhance their language skills or those embarking on a journey to learn English as a second language, this course offers a systematic exploration of the essential principles and rules governing the structure and usage of English.

Through a combination of theoretical explanations, practical examples, and interactive exercises, students will develop a solid understanding of the building blocks of English grammar. The course begins by introducing the basic components of grammar, such as parts of speech, sentence structure, and punctuation. Students will learn to identify and categorize nouns, verbs, adjectives, adverbs, prepositions, and conjunctions, as well as understand their roles and functions within sentences.

As the course progresses, students will delve deeper into various grammatical concepts, including tenses, verb forms, subject-verb agreement, pronouns, articles, and modifiers. They will gain a comprehensive understanding of how these elements work together to form grammatically correct and coherent sentences. Additionally, the course will address common grammatical errors and provide strategies to avoid them.

Throughout the course, students will have the opportunity to apply their knowledge through interactive exercises, quizzes, and practical assignments. They will refine their grammatical skills by analyzing and constructing sentences, identifying errors, and making appropriate corrections. The course fosters an environment conducive to active learning, encouraging students to engage in discussions and seek clarification on any challenging concepts.

By the end of the course, students will have acquired a solid foundation in English grammar, enabling them to communicate more effectively and confidently in both written and spoken forms. Whether they are native English speakers looking to strengthen their language skills or non-native English speakers aiming to master the fundamentals of grammar, this course provides a solid platform for further language development and advanced studies in English.',
            'level_id' => 1,
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $courseContent->id,
        ]);

        $course->admins()->attach(1);
        $courseContent = Content::create([
            'title' => ' course1 ',
            'content_type' => 'course',
        ]);

        $course = Course::create([
            'title' => 'title1',
            'description' => 'COURSE1',
            'level_id' => 1,
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $courseContent->id,
        ]);

        $course->admins()->attach(1);

        $courseContent = Content::create([
            'title' => ' course2 ',
            'content_type' => 'course',
        ]);

        $course = Course::create([
            'title' => 'title2',
            'description' => 'COURSE2',
            'level_id' => 1,
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $courseContent->id,
        ]);

        $course->admins()->attach(1);

        $courseContent = Content::create([
            'title' => ' course3 ',
            'content_type' => 'course',
        ]);

        $course = Course::create([
            'title' => 'title3',
            'description' => 'COURSE3',
            'level_id' => 1,
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $courseContent->id,
        ]);

        $course->admins()->attach(1);
        // learning objectives seeders

        $objective1 = LearningObjective::create([
            'name'=>'This That These Those',
            'course_id'=>1,
            'level'=>'Easy'
        ]);
        $objective2 = LearningObjective::create([
            'name'=>'Like, Likes, Don’t like, Doesn’t like ',
            'course_id'=>1,
            'level'=>'Easy'
        ]);
        $objective3 = LearningObjective::create([
            'name'=>'MUCH vs. MANY vs. A LOT OF',
            'course_id'=>1,
            'level'=>'Easy'
        ]);




        //lessons seeders


        $lessonContent1 = Content::create([
            'title' => "Demonstrative Pronouns: This, That, These, Those",
            'content_type' => 'lesson',
            'parent_id' => $courseContent->id,
            'order'=> 1,

        ]);
$duration=7;
        $lesson1 = Lesson::create([
            'title' => "Demonstrative Pronouns: This, That, These, Those",
            'description' => "In this lesson on demonstrative pronouns, students will learn how to use the words \"this,\" \"that,\" \"these,\" and \"those\" effectively to point out specific people, objects, or ideas in English. Through engaging activities and examples, students will grasp the differences between the singular and plural forms of demonstrative pronouns and understand how they indicate proximity and distance. By the end of the lesson, students will confidently navigate conversations and written texts, using demonstrative pronouns accurately to convey meaning and clarity.",
            'duration'=> $duration,
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $lessonContent1->id,
            'learning_objective_id'=>$objective1->id
        ]);
        ContentController::addToDuration($duration , $lessonContent1->parent_id);

        $levels = ['simple', 'medium', 'more explanation'];
        foreach ($levels as $level) {
            ExplanationLevel::create([
                'lesson_id' => $lesson1->id,
                'level' => $level
            ]);
        }

        $simpleExplanationLevel = ExplanationLevel::where('lesson_id', $lesson1->id)
            ->where('level', 'simple')
            ->first();

        $mediumExplanationLevel = ExplanationLevel::where('lesson_id', $lesson1->id)
            ->where('level', 'medium')
            ->first();

        $moreExplanationLevel = ExplanationLevel::where('lesson_id', $lesson1->id)
            ->where('level', 'more explanation')
            ->first();

        $page1=Page::create([
            'lesson_id'=>$lesson1->id,
            'explanation_level_id' => $simpleExplanationLevel->id,
            'order'=>1
        ]);

        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 1,
                'type' => 'title'
            ]
        );
        Title::create([
            'component_id' => $component->id,
            'body'=> 'This, that, these, those',
        ]);

        $page1=Page::create([
            'lesson_id'=>$lesson1->id,
            'explanation_level_id' => $simpleExplanationLevel->id,
            'order'=>2
        ]);

        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 1,
                'type' => 'title'
            ]
        );
        Title::create([
            'component_id' => $component->id,
            'body'=> 'This, that, these, those',
        ]);
        ///////////////////////////////////////////////////////////////////////////
        ///
        $page1=Page::create([
            'lesson_id'=>$lesson1->id,
            'explanation_level_id' => $simpleExplanationLevel->id,
            'order'=>3
        ]);

        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 1,
                'type' => 'title'
            ]
        );
        Title::create([
            'component_id' => $component->id,
            'body'=> 'This, that, these, those',
        ]);

        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 2,
                'type' => 'textarea'
            ]
        );

        TextArea::create([
            'component_id' => $component->id,
            'body'=> "<p>We can use this and these to talk about things near us. We can use that and those to talk about things far away.</p>
    <ul>
        <li>This book is my favourite.</li>
        <li>That is my sister in the garden.</li>
        <li>These are my two best friends.</li>
        <li>Those pens don't work.</li>
    </ul>",
        ]);
        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 3,
                'type' => 'title'
            ]
        );
        Title::create([
            'component_id' => $component->id,
            'body'=> 'How to use them',
        ]);
        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 4,
                'type' => 'textarea'
            ]
        );

        TextArea::create([
            'component_id' => $component->id,
            'body'=> "<p>Use this and these to talk about things near us. Use this for one thing and these for more than one.</p>
    <ul>
        <li>This is my brother here.</li>
        <li>These games here are not mine.</li>
    </ul>",
        ]);
        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 5,
                'type' => 'textarea'
            ]
        );

        TextArea::create([
            'component_id' => $component->id,
            'body'=> "<p>Use that and those to talk about things far away. Use that for one thing and those for more than one.</p>
    <ul>
        <li>That teacher over there teaches English.</li>
        <li>Are those your toys over there?</li>
    </ul>",
        ]);
        $page1=Page::create([
            'lesson_id'=>$lesson1->id,
            'explanation_level_id' => $mediumExplanationLevel->id,
            'order'=>1
        ]);
        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );

        TextArea::create([
            'component_id' => $component->id,
            'body' => '<p>Demonstrative Pronouns:</p>
    <ul>
        <li>"This," "that," "these," and "those" are demonstrative pronouns used to indicate the location or proximity of objects or people in relation to the speaker.</li>
    </ul>
<p>Proximity</p>
<ul>
    <li>"This" and "these" are used to refer to things or people that are near the speaker or within close proximity.</li>
    <li>For example: "This book is my favorite" (referring to a book that is close to the speaker), "These are my two best friends" (referring to friends who are near the speaker).</li>
    <li>"That" and "those" are used to refer to things or people that are far away from the speaker or located at a distance.</li>
    <li>For example: "That is my sister in the garden" (referring to the sister who is far from the speaker), "Those pens don\'t work" (referring to pens that are located far away).</li>
</ul>',
        ]);

        $page1=Page::create([
            'lesson_id'=>$lesson1->id,
            'explanation_level_id' => $mediumExplanationLevel->id,
            'order'=>2
        ]);
        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component->id,
            'body' => '<p>Singular and Plural Forms:</p>
<ul>
<li>This" is used when referring to a single object or person that is near the speaker.</li>
    <li>For example: "This book is my favorite" (referring to one book that is close to the speaker).</li>
    <li>"That" is used when referring to a single object or person that is far away from the speaker.</li>
    <li>For example: "That teacher over there teaches English" (referring to one teacher who is located at a distance).</li>
    <li>"These" is used when referring to multiple objects or people that are near the speaker.</li>
    <li>For example: "These are my two best friends" (referring to friends who are close to the speaker).</li>
    <li>"Those" is used when referring to multiple objects or people that are far away from the speaker.</li>
    <li>For example: "Are those your toys over there?" (referring to toys that are located at a distance).</li>
</ul>
<p>In summary, "this" and "these" are used to indicate objects or people that are near the speaker, while "that" and "those" are used to indicate objects or people that are far away from the speaker. "This" and "that" are used for singular nouns, while "these" and "those" are used for plural nouns.</p>',
        ]);

        $page1=Page::create([
            'lesson_id'=>$lesson1->id,
            'explanation_level_id' => $moreExplanationLevel->id,
            'order'=>1
        ]);
        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 1,
                'type' => 'video'
            ]
        );
        Video::create([
           'component_id'=>$component->id,
            'url'=>'https://youtu.be/GIbD5seHH-E',
        ]);
        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 2,
                'type' => 'textarea'
            ]
        );

        TextArea::create([
            'component_id' => $component->id,
            'body'=> '<p>In the first section, you will see Rob Woodward pointing to different things.</p>
<p>Sometimes he points to one thing that is close (<em>THIS</em>)</p>
<p>Sometimes he points to one thing that is far/at a distance (<em>THAT</em>)</p>
<p>Sometimes he points to two or more things that are close (<em>THESE</em>)</p>
<p>Sometimes he points to two or more things that are far/at a distance (<em>THOSE</em>)</p>
<p>The correct demonstrative will appear written on the screen and you will hear its pronunciation.</p>
<p>Make sure you listen to the difference in pronunciation of <em>THIS</em> and <em>THESE</em>.</p>
<p>In the second section, a summary chart appears then an exercise where the viewer needs to decide which demonstrative (<em>this, that, these, those</em>) needs to be used in each situation. A finger points to a thing or things that are close or far from it.</p>
<p>Finally, a second summary chart appears where <em>this/that/these/those</em> are used as demonstrative adjectives before the word "book" or "books".</p>
<p><em>This book</em> - <em>These books</em> - <em>That book</em> - <em>Those books</em>.</p>',
        ]);

        $lessonContent1 = Content::create([
            'title' => "Like and don't like",
            'content_type' => 'lesson',
            'parent_id' => $courseContent->id,
            'order'=> 2,

        ]);
$duration=10;
        $lesson1 = Lesson::create([
            'title' => "Like and don't like",
            'description' => 'In this lesson on demonstrative pronouns, students will learn how to use the words \"this,\" \"that,\" \"these,\" and \"those\" effectively to point out specific people, objects, or ideas in English. Through engaging activities and examples, students will grasp the differences between the singular and plural forms of demonstrative pronouns and understand how they indicate proximity and distance. By the end of the lesson, students will confidently navigate conversations and written texts, using demonstrative pronouns accurately to convey meaning and clarity.',
            'duration'=> $duration,
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $lessonContent1->id,
            'learning_objective_id'=>$objective2->id
        ]);
        ContentController::addToDuration($duration , $lessonContent1->parent_id);

        $levels = ['simple', 'medium', 'more explanation'];
        foreach ($levels as $level) {
            ExplanationLevel::create([
                'lesson_id' => $lesson1->id,
                'level' => $level
            ]);
        }

        $simpleExplanationLevel = ExplanationLevel::where('lesson_id', $lesson1->id)
            ->where('level', 'simple')
            ->first();
        $page1=Page::create([
            'lesson_id'=>$lesson1->id,
            'explanation_level_id' => $simpleExplanationLevel->id,
            'order'=>1
        ]);

        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 1,
                'type' => 'title'
            ]
        );
        Title::create([
            'component_id' => $component->id,
            'body'=> "Like and don't like",
        ]);
        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 2,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component->id,
            'body'=> "<p>We can use like and don't like to say things are good or bad.</p>
<ul>
  <li>I like chocolate.</li>
  <li>She likes cats.</li>
  <li>We don't like vegetables.</li>
</ul>
",
        ]);
        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 3,
                'type' => 'title'
            ]
        );
        Title::create([
            'component_id' => $component->id,
            'body'=> "How to use them",
        ]);
        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 4,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component->id,
            'body'=> "<p>Use like and don't like for I, you, we and they.</p>
<ul>
  <li>I like apples.</li>
  <li>You like the park.</li>
  <li>We don't like snakes.</li>
  <li>They don't like the rain.</li>
</ul>",
        ]);

        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 5,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component->id,
            'body'=> "<p>Use likes and doesn't like for he, she and it.</p>
<ul>
    <li>He likes chocolate.</li>
    <li>She doesn't like the zoo.</li>
    <li>It doesn't like cold water.</li>
</ul>",
        ]);

        $component=Component::create([
                'page_id' => $page1->id,
                'order' => 6,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component->id,
            'body'=> "<p>Make questions with do for I, you, we and they and with does for he, she and it.</p>
<ul>
  <li>Do you like bananas? Yes, I do.</li>
  <li>Does he like the beach? No, he doesn't.</li>
  <li>What vegetables do they like?</li>
</ul>",
        ]);
        $content = Content::create([
            'title' => 'Assessment for lesson 1',
            'content_type' => 'assessment',
            'parent_id' => 2,
            'order' => 3,
        ]);

        $duration = 5;
        $assessment = Assessment::create([
            'title' => 'Assessment for lesson 1',
            'description' => 'This assessment test your understanding for the lesson you must have 80% to pass the assessment',
            'duration'=> $duration,
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $content->id,
            'learning_objective_id'=>1
        ]);
        $assessment->save();
        ContentController::addToDuration($duration , $content->parent_id);

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



        $content = Content::create([
            'title' => 'Assessment for lesson 2',
            'content_type' => 'assessment',
            'parent_id' => 3,
            'order' => 4,
        ]);

        $duration = 5;
        $assessment = Assessment::create([
            'title' => 'Assessment for lesson 2',
            'description' => 'This assessment test your understanding for the lesson you must have 80% to pass the assessment',
            'duration'=> $duration,
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $content->id,
            'learning_objective_id'=>2
        ]);
        $assessment->save();
        ContentController::addToDuration($duration , $content->parent_id);

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
                'text'=>'I _______ fruit.',
            ]
        );
        $options1 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "like"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "likes"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "liking"
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
                'text'=>'She _______ homework.',
            ]
        );
        $options1 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "like"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "does like"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "likes"
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
                'text'=>'Does she _______ volleyball??',
            ]
        );
        $options1 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "likes"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "liked"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "like"
            ],
        ];
        DB::table('options')->insert($options1);

    }







}
