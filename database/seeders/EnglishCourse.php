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
use App\Models\Review;
use App\Models\User;
use Choice\MultiChoiceQuestion;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class EnglishCourse extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $course = Course::factory()->create([
            'title' => 'Foundations of English: Mastering Pronouns, Verb to Be, and Greetings',
            'description' => 'Welcome to the Foundations of English course! In this comprehensive English course, you will develop a strong foundation in key language concepts and skills. From subject pronouns and adjectives to the verb "to be" and essential greetings, this course will provide you with the essential knowledge needed to communicate effectively in English.',
            'level_id' => 1,
            'status_id' => 1,
            'creator_id' => 1
        ]);

        $review = new Review([
            'star' => 5,
            'comment' => $faker->sentence,
        ]);
        $user = User::find(1);
        $review->course()->associate($course);
        $review->user()->associate($user);
        $review->save();

        $user = User::find(2);
        $review = new Review([
            'star' => 3.5,
            'comment' => $faker->sentence,
        ]);
        $review->course()->associate($course);
        $review->user()->associate($user);
        $review->save();
        $review = new Review([
            'star' => 3,
            'comment' => $faker->sentence,
        ]);
        $review->course()->associate($course);
        $review->user()->associate($user);
        $review->save();

        $review = new Review([
            'star' => 4,
            'comment' => $faker->sentence,
        ]);
        $review->course()->associate($course);
        $review->user()->associate($user);
        $review->save();
        $review = new Review([
            'star' => 3.5,
            'comment' => $faker->sentence,
        ]);
        $review->course()->associate($course);
        $review->user()->associate($user);
        $review->save();
        $review = new Review([
            'star' => 2,
            'comment' => $faker->sentence,
        ]);
        $review->course()->associate($course);
        $review->user()->associate($user);
        $review->save();


        $learningObjective = LearningObjective::factory()->create([
            'name'=>'learners will be able to identify and correctly use subject pronouns and adjectives in English sentences, demonstrating accurate grammatical usage and understanding their role in sentence structure.',
            'course_id'=> $course->content->id,
            'level'=> 'Beginner'
        ]);
        $learningObjective2 = LearningObjective::factory()->create([
            'name'=>'learners will be able to comprehend and apply the verb "to be" in its various forms, including affirmative, negative, and interrogative sentences. They will demonstrate proficiency in using the verb to express states, identities, and relationships.',
            'course_id'=> $course->content->id,
            'level'=> 'Beginner'
        ]);
        $learningObjective3 = LearningObjective::factory()->create([
            'name'=>'learners will be able to confidently greet others using common English expressions such as "Hi" and "Hello." They will also acquire the ability to ask and respond to the question "How are you?" appropriately, displaying cultural awareness and using suitable responses.',
            'course_id'=> $course->content->id,
            'level'=> 'Beginner'
        ]);
        $lesson1 = Lesson::factory()
            ->withPrentAndOrder($course->content->id,1)
            ->withObjective($learningObjective->id)
            ->create([
                'title' => "Subject Pronouns and Adjectives",
                'description' => "In this lesson, you will delve into the fundamentals of subject pronouns and adjectives in English. You will learn how subject pronouns replace nouns to avoid repetition and how adjectives describe and modify nouns. Through interactive exercises and examples, you will gain a solid understanding of how to correctly use subject pronouns and adjectives in sentences, allowing you to communicate more effectively and express yourself with precision.",
                'duration'=> 25,
                'status_id' => 1,
                'creator_id' => 1,
            ]);
        $lesson2 = Lesson::factory()
            ->withPrentAndOrder($course->content->id,3)
            ->withObjective($learningObjective2->id)
            ->create([
                'title' => "Verb to Be",
                'description' => "The verb 'to be' is an essential component of English grammar. In this lesson, you will explore the various forms and functions of the verb 'to be.' From affirmatives to negatives and interrogatives, you will grasp the nuances of using this versatile verb. Through engaging activities and practice exercises, you will become adept at employing the verb 'to be' to express states, identities, and relationships. By the end of this lesson, you will have a firm grasp of this foundational verb and its usage in different contexts.",
                'duration'=> 15,
                'status_id' => 1,
                'creator_id' => 1,
            ]);
        $lesson3 = Lesson::factory()
            ->withPrentAndOrder($course->content->id,5)
            ->withObjective($learningObjective3->id)
            ->create([
                'title' => "Greetings (Hi, Hello, and Asking/Answering 'How are you?')",
                'description' => "Greetings are an essential part of everyday communication. In this lesson, you will learn the art of greeting others in English. You will discover the nuances of using common greetings such as 'Hi' and 'Hello' appropriately in various situations. Additionally, you will explore the social convention of asking and answering the question 'How are you?' You will understand the different responses and learn how to provide appropriate answers based on the context and your emotional state. By the end of this lesson, you will feel confident in initiating conversations and engaging in polite exchanges using greetings and basic inquiries about well-being.",
                'duration'=> 15,
                'status_id' => 1,
                'creator_id' => 1,
            ]);
        $course->duration += $lesson1->duration;
        $course->duration += $lesson2->duration;
        $course->duration += $lesson3->duration;
        $course->save();

        $explanationLevel = $lesson1->explanationLevel('simple');

        $page = Page::create([
            'lesson_id' => $lesson1->id,
            'explanation_level_id' => $explanationLevel->id,
            'order' => 1,
        ]);

        Title::factory()
            ->withPageAndOrder($page->id, 1)
            ->create([
                'body' => 'Pronouns - الضمائر'
            ]);

        TextArea::factory()
            ->withPageAndOrder($page->id, 2)
            ->create([
                'body' => '<p class="py-8" style="text-align:center"><span style="font-size:48px">I&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; =&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; أنا</span></p>
                '
            ]);

        $explanationLevel = $lesson1->explanationLevel('simple');

        $page2 = Page::create([
            'lesson_id' => $lesson1->id,
            'explanation_level_id' => $explanationLevel->id,
            'order' => 2,
        ]);

        Title::factory()
            ->withPageAndOrder($page2->id, 1)
            ->create([
                'body' => 'Pronouns - الضمائر'
            ]);

        TextArea::factory()
            ->withPageAndOrder($page2->id, 2)
            ->create([
                'body' => '<p class="py-8" style="text-align:center"><span style="font-size:48px">You&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; =&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;أنتَ أو أنتِ أو أنتم</span></p>
                '
            ]);

        TextArea::factory()
        ->withPageAndOrder($page2->id, 3)
        ->create([
            'body' => '<p class="py-1" style="text-align:center"><span style="font-size:48px">قد تأتي بقصد المفرد ويقابلها باللغة العربيةأنتَ للمفرد المذكر أو أنتِ للمفرد المؤنث</span></p>
            '
        ]);

        TextArea::factory()
        ->withPageAndOrder($page2->id, 4)
        ->create([
            'body' => '<p class="py-1" style="text-align:center"><span style="font-size:48px">وقد تأتي بقصد الجمع والمثنى ويقابلها باللغة العربية أنتما للمذكر أو أنتن للمؤنث وأنتما للمثنى المذكر والمؤنث</span></p>
            '
        ]);

        $explanationLevel = $lesson1->explanationLevel('simple');


        $page3 = Page::create([
            'lesson_id' => $lesson1->id,
            'explanation_level_id' => $explanationLevel->id,
            'order' => 3,
        ]);

        Title::factory()
            ->withPageAndOrder($page3->id, 1)
            ->create([
                'body' => 'Pronouns - الضمائر'
            ]);

        TextArea::factory()
            ->withPageAndOrder($page3->id, 2)
            ->create([
                'body' => '<p class="py-8" style="text-align:center"><span style="font-size:48px">We&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; =&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;نحن</span></p>
                '
            ]);

        TextArea::factory()
        ->withPageAndOrder($page3->id, 3)
        ->create([
            'body' => '<p class="py-2" style="text-align:center"><span style="font-size:48px">He&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; =&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;هو للمفرد المذكر</span></p>
            '
        ]);

        TextArea::factory()
        ->withPageAndOrder($page3->id, 4)
        ->create([
            'body' => '<p class="py-2" style="text-align:center"><span style="font-size:48px">She&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; =&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;هي للمفرد المؤنث</span></p>
            '
        ]);

        TextArea::factory()
        ->withPageAndOrder($page3->id, 5)
        ->create([
            'body' => '<p class="py-2" style="text-align:center"><span style="font-size:48px">It&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; =&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;هو أو هي ولكن تأتي للأشياء أو الحيوانات</span></p>
            '
        ]);

        TextArea::factory()
        ->withPageAndOrder($page3->id, 6)
        ->create([
            'body' => '<p class="py-2" style="text-align:center"><span style="font-size:48px">They&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; =&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;هما, هم, وهنَّ</span></p>
            '
        ]);

        $explanationLevel = $lesson1->explanationLevel('medium');


        $page4 = Page::create([
            'lesson_id' => $lesson1->id,
            'explanation_level_id' => $explanationLevel->id,
            'order' => 4,
        ]);

        Title::factory()
            ->withPageAndOrder($page4->id, 1)
            ->create([
                'body' => 'Pronouns - الضمائر'
            ]);


        TextArea::factory()
        ->withPageAndOrder($page4->id, 2)
        ->create([
            'body' => '<p class="py-2" style="text-align:center"><span style="font-size:48px">They&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; =&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;هما, هم, وهنَّ</span></p>
            '
        ]);


        TextArea::factory()
        ->withPageAndOrder($page4->id, 3)
        ->create([
            'body' => '<p style="text-align:right">لا يوجد فرق في اللغة الانكليزية بين المذكر والمؤنث في حالة المخاطب كما هو الحال في اللغة العربية , ففي اللغة العربية نفرق بين المذكر والمؤنث بالكسرة والفتحة.&nbsp;</p>

            <p style="text-align:right">مثال: نقول للمذكر المخاطب أنتَ بالفتحة بينما نقول للمؤنث المخاطب أنتِ بالكسرة.</p>

            <p style="text-align:right">وأيضا تنطبق القاعدة على الأفعال, مثال: أكلتَ بالفتحة للمذكر وأكلتِ بالكسرة للمؤنث</p>

            <p style="text-align:right">&nbsp;</p>

            <table border="1" cellpadding="1" cellspacing="1" style="width:500px">
                <thead>
                    <tr>
                        <th scope="col">باللغة الانكليزية</th>
                        <th scope="col">باللغة العربية</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align:center">I</td>
                        <td style="text-align:center">أنا</td>
                    </tr>
                    <tr>
                        <td style="text-align:center">You</td>
                        <td style="text-align:center">أنتَ - أنتِ - أنتما - أنتم - أنتن</td>
                    </tr>
                    <tr>
                        <td style="text-align:center">He</td>
                        <td style="text-align:center">هو</td>
                    </tr>
                    <tr>
                        <td style="text-align:center">She</td>
                        <td style="text-align:center">هي</td>
                    </tr>
                    <tr>
                        <td style="text-align:center">It</td>
                        <td style="text-align:center">هو أو هي للأشياء</td>
                    </tr>
                    <tr>
                        <td style="text-align:center">We</td>
                        <td style="text-align:center">نحن</td>
                    </tr>
                    <tr>
                        <td style="text-align:center">They</td>
                        <td style="text-align:center">هما - هم - هن</td>
                    </tr>
                </tbody>
            </table>

            <p>&nbsp;</p>
            '
        ]);

        $explanationLevel = $lesson1->explanationLevel('more explanation');


        $page5 = Page::create([
            'lesson_id' => $lesson1->id,
            'explanation_level_id' => $explanationLevel->id,
            'order' => 5,
        ]);

        Title::factory()
            ->withPageAndOrder($page5->id, 1)
            ->create([
                'body' => 'Pronouns - الضمائر'
            ]);


        Video::factory()
                ->withPageAndOrder($page5->id, 2)
                ->create([
                    'url' => 'https://www.youtube.com/watch?v=SGS_G-2FwQE'
                ]);

        $explanationLevel = $lesson2->explanationLevel('simple');

        $page = Page::create([
            'lesson_id' => $lesson2->id,
            'explanation_level_id' => $explanationLevel->id,
            'order' => 1,
        ]);

        Title::factory()
            ->withPageAndOrder($page->id, 1)
            ->create([
                'body' => 'الفعل الكون : Verb to be'
            ]);

        TextArea::factory()
            ->withPageAndOrder($page->id, 2)
            ->create([
                'body' => '<p>في الحاضر</p>
                            <p>am :المفرد المتكلم </p>
                            <p>:مثال:تصبح الجملة أنا أشرب</p>
                            <p> I am drinking </p>
                            <p>are : المفرد المخاطب</p>
                            <p>:مثال: تصبح الجملة أنت تشرب</p>
                            <p>You are drinking</p>
                            <p>is :المفرد الغائب</p>
                            <p>:مثال: تصبح الجملة هي تشرب /هو يشرب</p>
                            <p>He/She is drinking</p>
                            <p>are :الجمع في جميع الحالات</p>
                            <p>:مثال: تصبح الجملة هم يشربون/ نحن نشرب</p>
                            <p>We/They are drinking</p>'
            ]);

        $explanationLevel = $lesson2->explanationLevel('medium');

        $page = Page::create([
            'lesson_id' => $lesson2->id,
            'explanation_level_id' => $explanationLevel->id,
            'order' => 1,
        ]);

        Title::factory()
            ->withPageAndOrder($page->id, 1)
            ->create([
                'body' => 'الفعل الكون : Verb to be'
            ]);

        TextArea::factory()
            ->withPageAndOrder($page->id, 2)
            ->create([
                'body' => '<p>في اللغة الانجليزية , الأفعال التي نقوم بها حاليا تمثل حالة , فنحتاج الأفعال التي قبلها الى ان تستخدم فعل الكون لكي نقول أنا أشرب ولكن ترجمتها الحرفية تكون: أنا أقوم بالشرب </p>'
            ]);

        $explanationLevel = $lesson2->explanationLevel('more explanation');

        $page = Page::create([
            'lesson_id' => $lesson2->id,
            'explanation_level_id' => $explanationLevel->id,
            'order' => 1,
        ]);

        Title::factory()
            ->withPageAndOrder($page->id, 1)
            ->create([
                'body' => 'الفعل الكون : Verb to be'
            ]);

        Video::factory()
            ->withPageAndOrder($page->id, 2)
            ->create([
                'url' => 'https://www.youtube.com/watch?v=BHFXrO7ZSbo'
            ]);


        $explanationLevel = $lesson3->explanationLevel('simple');

        $page = Page::create([
            'lesson_id' => $lesson3->id,
            'explanation_level_id' => $explanationLevel->id,
            'order' => 1,
        ]);

        Title::factory()
            ->withPageAndOrder($page->id, 1)
            ->create([
                'body' => 'Greetings'
            ]);

        TextArea::factory()
            ->withPageAndOrder($page->id, 2)
            ->create([
                'body' => '<p>:السؤال عن الحال </p>
<p>كما مر معنا سابقا عند السؤال عن الحال نحتاج في اللغة الانجليزية إلى فعل الكون.</p>
<p>فنقول في اللغة العربية: "كيف حالك؟" بينما في اللغة الانجليزية نقول حرفياً "كيف يكون حالك؟" (<em>How are you?</em>).</p>
<p>نلاحظ قدوم الفعل "are" قبل الضمير "you" في حالة السؤال، وهذا يحدث أيضًا في اللغة العربية. عندما نسأل عن حال شخص ما، يأتي الفاعل في نهاية الجملة عادةً.</p>
<p>مثال: "كيف حال عمر؟" أو "كيف حالك؟"</p>
<p>أما في الجواب، يكون الفاعل في بداية الجملة.</p>
<p>مثال: "Omar is good" أو "I am good".</p>'
            ]);

        $explanationLevel = $lesson3->explanationLevel('medium');

        $page = Page::create([
            'lesson_id' => $lesson3->id,
            'explanation_level_id' => $explanationLevel->id,
            'order' => 1,
        ]);

        Video::factory()
            ->withPageAndOrder($page->id, 2)
            ->create([
                'url' => 'https://www.youtube.com/watch?v=BHFXrO7ZSbo'
            ]);

        $explanationLevel = $lesson3->explanationLevel('more explanation');

        $page = Page::create([
            'lesson_id' => $lesson3->id,
            'explanation_level_id' => $explanationLevel->id,
            'order' => 1,
        ]);

        TextArea::factory()
            ->withPageAndOrder($page->id, 1)
            ->create([
                'body' => '<p>:السؤال عن الحال </p>
<p>كما مر معنا سابقا عند السؤال عن الحال نحتاج في اللغة الانجليزية إلى فعل الكون.</p>
<p>فنقول في اللغة العربية: "كيف حالك؟" بينما في اللغة الانجليزية نقول حرفياً "كيف يكون حالك؟" (<em>How are you?</em>).</p>
<p>نلاحظ قدوم الفعل "are" قبل الضمير "you" في حالة السؤال، وهذا يحدث أيضًا في اللغة العربية. عندما نسأل عن حال شخص ما، يأتي الفاعل في نهاية الجملة عادةً.</p>
<p>مثال: "كيف حال عمر؟" أو "كيف حالك؟"</p>
<p>أما في الجواب، يكون الفاعل في بداية الجملة.</p>
<p>مثال: "Omar is good" أو "I am good".</p>'
            ]);

        Video::factory()
            ->withPageAndOrder($page->id, 2)
            ->create([
                'url' => 'https://www.youtube.com/watch?v=BHFXrO7ZSbo'
            ]);



        $assessment = Assessment::factory()
            ->withPrentAndOrder($course->content->id,4)
            ->withObjective($learningObjective2->id)
            ->create([
                'title' => "Verb to Be",
            ]);
        $course->duration += $assessment->duration;

        $question3=Question::create([
                'assessment_id' => $assessment->id,
                'order' => 1,
                'type' => 'multi-choice',
                'points'=>10
            ]
        );
        $assessmet = $question3->assessment;
        $assessmet->points += $question3->points;
        $assessmet->save();
        $multiquestion1=MultiChoiceQuestion::create([
                'question_id'=>$question3->id,
                'text'=>'I _______ eating an apple.',
            ]
        );
        $options3 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "am"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "is"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "are"
            ],
        ];
        DB::table('options')->insert($options3);


        $question3=Question::create([
                'assessment_id' => $assessment->id,
                'order' => 2,
                'type' => 'multi-choice',
                'points'=>10
            ]
        );
        $assessmet = $question3->assessment;
        $assessmet->points += $question3->points;
        $assessmet->save();
        $multiquestion1=MultiChoiceQuestion::create([
                'question_id'=>$question3->id,
                'text'=>'You _______ doing jumping jacks.',
            ]
        );
        $options3 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "is"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "am"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "are"
            ],
        ];
        DB::table('options')->insert($options3);

        $question3=Question::create([
                'assessment_id' => $assessment->id,
                'order' => 3,
                'type' => 'multi-choice',
                'points'=>10
            ]
        );
        $assessmet = $question3->assessment;
        $assessmet->points += $question3->points;
        $assessmet->save();
        $multiquestion1=MultiChoiceQuestion::create([
                'question_id'=>$question3->id,
                'text'=>'He _______ swimming.',
            ]
        );
        $options3 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "am"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "is"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "are"
            ],
        ];
        DB::table('options')->insert($options3);

        $course->save();

        $assessment = Assessment::factory()
            ->withPrentAndOrder($course->content->id,2)
            ->withObjective($learningObjective->id)
            ->create([
                'title' => "Subject Pronouns and Adjectives",
            ]);
        $course->duration += $assessment->duration;
        $course->save();
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
                'text'=>'the cat is eating ,/:الضمير المناسب للقطة',
            ]
        );
        $options1 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "it"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "she"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "he"
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
                'text'=>'hi everybody , _____ are a good people./مرحبا جميعاانتم اشخاص جيدون ',
            ]
        );
        $options1 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "you"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "we"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "they"
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
                'text'=>'How are _____?/كيف حالك؟',
            ]
        );
        $options1 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "you"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "she"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "it"
            ],
        ];
        DB::table('options')->insert($options1);

        $assessment = Assessment::factory()
            ->withPrentAndOrder($course->content->id,6)
            ->withObjective($learningObjective3->id)
            ->create([
                'title' => "Greetings (Hi, Hello, and Asking/Answering 'How are you?')",
            ]);
        $course->duration += $assessment->duration;
        $course->save();
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
                'text'=>'معنى كلمة كيف حالك باللغة الانكليزية:',
            ]
        );
        $options1 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "how are you?"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "how is you?"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "how am you?"
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
                'text'=>'ما الطريقة الرسمية في قول مرحبا:',
            ]
        );
        $options1 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "hello"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "hi"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "how are you?"
            ],
        ];
        DB::table('options')->insert($options1);

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
                'text'=>'كيف حال عمر؟',
            ]
        );
        $options1 = [
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => true,
                "text" => "how is omar?"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "how are omar?"
            ],
            [
                "multi_choice_question_id" => $multiquestion1->id,
                "is_correct" => false,
                "text" => "how am omar?"
            ],
        ];
        DB::table('options')->insert($options1);

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
