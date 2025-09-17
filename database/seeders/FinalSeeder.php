<?php

namespace Database\Seeders;

use App\Models\Components\Page;
use App\Models\Course;
use App\Models\LearningObjective;
use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FinalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course = Course::create([
            'title'=>'Foundations of English',
            'description' => 'سوف نبدأ بهذا البحث البسيط للمبتدئين باللغة الانكليزية وهو يحتوي على مواضيع بسيطة وجمل بسيطة وأي شخص يستطيع أن يفهمها',
            'level_id'=>1,
            'creator_id' => 2
        ]);
        $course->admins()->attach(2);
        Zone::create(['title'=>'Basics الأساسيات', 'description'=>'يحتوي هذا الدرس على الأحرف و أنواعها','course_id'=>1,'level'=>1]);
        Zone::create(['title'=>'Numbers الأرقام', 'description'=>'يحتوي هذا الدرس على الأرقام الأساسية و تركيبات الأرقام','course_id'=>1,'level'=>1]);
        Zone::create(['title'=>'Nouns الأسماء', 'description'=>'يحتوي هذا الدرس على الأسماء و النكرة و المعرفة و الجموع','course_id'=>1,'level'=>2]);
        Zone::create(['title'=>'Pronouns 1 الضمائر', 'description'=>'يحتوي هذا الدرس على الضمائر و بعض أنواعها','course_id'=>1,'level'=>2]);
        Zone::create(['title'=>'Pronouns 2 الضمائر', 'description'=>'يحتوي هذا الدرس على الضمائر و بعض أنواعها','course_id'=>1,'level'=>2]);
        Zone::create(['title'=>'Verbs الأفعال', 'description'=>'يحتوي هذا الدرس على الأفعال و أنواعها بالاضافة الى بعض الحالات الشاذة و الأزمنة','course_id'=>1,'level'=>2]);
        Zone::create(['title'=>'Adjectives الصفات', 'description'=>'يحتوي هذا الدرس على الصفات و أنواعها بالاضافة الى بعض الحالات الشاذة ','course_id'=>1,'level'=>3]);
        Zone::create(['title'=>'Auxilary Verbs الأفعال المساعدة', 'description'=>'يحتوي هذا الدرس على الأفعال المساعدة و أنواعها مع الأزمنة لكل منها ','course_id'=>1,'level'=>3]);

        $objectivesData = [
            ['id' => 1, 'name' => 'يهدف هذا الدرس لتعليم الطالب الأحرف الانكليزية و كتابتها و التفريق بين كبيرها و صغيرها','zone_id' => 1],
            ['id' => 2, 'name' => 'يهدف هذا الدرس لتعليم الطالب الأحرف الانكليزية الصوتية و الساكنة','zone_id' => 1],
            ['id' => 3, 'name' => 'يهدف هذا الدرس لتعليم الطالب الأرقام في اللغة الانكليزية من واحد و حتى التسعة','zone_id' => 2],
            ['id' => 4, 'name' => 'يهدف هذا الدرس لتعليم الطالب الأرقام في اللغة الانكليزية من مضاعفات العشرة','zone_id' => 2],
            ['id' => 5, 'name' => 'يهدف هذا الدرس لتعليم الطالب الأرقام في اللغة الانكليزية من مضاعفات المئة','zone_id' => 2],
            ['id' => 6, 'name' => 'يهدف هذا الدرس لتعليم الطالب الأرقام في اللغة الانكليزية من مضاعفات الألف','zone_id' => 2],
            ['id' => 7, 'name' => 'يهدف هذا الدرس لتعليم الطالب تركيب الأرقام في اللغة الانكليزية','zone_id' => 2],
            ['id' => 8, 'name' => 'يهدف هذا الدرس لتعليم الطالب قواعد الأسماء في اللغة الانكليزية','zone_id' => 3],
            ['id' => 9, 'name' => 'يهدف هذا الدرس لتعليم الطالب جمع الأسماء في اللغة الانكليزية','zone_id' => 3],
            ['id' => 10, 'name' => 'يهدف هذا الدرس لتعليم الطالب أدوات التعريف النكرة و المعرفة في اللغة الانكليزية','zone_id' => 3],
            ['id' => 11, 'name' => 'يهدف هذا الدرس لتعليم الطالب الأسماء المعدودة و غير المعدودة في اللغة الانكليزية','zone_id' => 3],
            ['id' => 12, 'name' => 'يهدف هذا الدرس لتعليم الطالب الضمائر الشخصية في اللغة الانكليزية','zone_id' => 4],
            ['id' => 13, 'name' => 'يهدف هذا الدرس لتعليم الطالب ضمائر المفعول به في اللغة الانكليزية','zone_id' => 4],
            ['id' => 14, 'name' => 'يهدف هذا الدرس لتعليم الطالب ضمائر الملكية في اللغة الانكليزية','zone_id' => 4],
            ['id' => 15, 'name' => 'يهدف هذا الدرس لتعليم الطالب الضمائر الإنعكاسية في اللغة الانكليزية','zone_id' => 4],
            ['id' => 16, 'name' => 'يهدف هذا الدرس لتعليم الطالب الضمائر الإستفهام في اللغة الانكليزية','zone_id' => 5],
            ['id' => 17, 'name' => 'يهدف هذا الدرس لتعليم الطالب الضمائر الوصل في اللغة الانكليزية','zone_id' => 5],
            ['id' => 18, 'name' => 'يهدف هذا الدرس لتعليم الطالب قواعد الأفعال في اللغة الانكليزية','zone_id' => 6],
            ['id' => 19, 'name' => 'يهدف هذا الدرس لتعليم الطالب الأفعال الماضية في اللغة الانكليزية','zone_id' => 6],
            ['id' => 20, 'name' => 'يهدف هذا الدرس لتعليم الطالب الأفعال المضارعة في اللغة الانكليزية','zone_id' => 6],
            ['id' => 21, 'name' => 'يهدف هذا الدرس لتعليم الطالب أفعال الأمر في اللغة الانكليزية','zone_id' => 6],
            ['id' => 22, 'name' => 'يهدف هذا الدرس لتعليم الطالب الصفات الوصفية في اللغة الانكليزية','zone_id' => 7],
            ['id' => 23, 'name' => 'يهدف هذا الدرس لتعليم الطالب صفات اسم العلم في اللغة الانكليزية','zone_id' => 7],
            ['id' => 24, 'name' => 'يهدف هذا الدرس لتعليم الطالب صفات الملكية في اللغة الانكليزية','zone_id' => 7],
            ['id' => 25, 'name' => 'يهدف هذا الدرس لتعليم الطالب صفات التفضيل و التقليل في اللغة الانكليزية','zone_id' => 7],
            ['id' => 26, 'name' => 'يهدف هذا الدرس لتعليم الطالب توضع الصفات في اللغة الانكليزية','zone_id' => 7],
            ['id' => 27, 'name' => 'يهدف هذا الدرس لتعليم الطالب الأفعال المساعدة الرئيسية في اللغة الانكليزية','zone_id' => 8],
            ['id' => 28, 'name' => 'يهدف هذا الدرس لتعليم الطالب الأفعال المساعدة الناقصة في اللغة الانكليزية','zone_id' => 8],
        ];
        foreach ($objectivesData as $objectiveData) {
            $objective = LearningObjective::create($objectiveData);
        }
        $relationships = [
            ['parent_id' => 1, 'child_id' => 2],
            ['parent_id' => 1, 'child_id' => 8],
            ['parent_id' => 1, 'child_id' => 12],
            ['parent_id' => 1, 'child_id' => 16],
            ['parent_id' => 1, 'child_id' => 17],
            ['parent_id' => 1, 'child_id' => 18],
            ['parent_id' => 3, 'child_id' => 4],
            ['parent_id' => 3, 'child_id' => 5],
            ['parent_id' => 3, 'child_id' => 6],
            ['parent_id' => 4, 'child_id' => 7],
            ['parent_id' => 5, 'child_id' => 7],
            ['parent_id' => 6, 'child_id' => 7],
            ['parent_id' => 8, 'child_id' => 9],
            ['parent_id' => 8, 'child_id' => 10],
            ['parent_id' => 9, 'child_id' => 11],
            ['parent_id' => 10, 'child_id' => 11],
            ['parent_id' => 11, 'child_id' => 22],
            ['parent_id' => 11, 'child_id' => 23],
            ['parent_id' => 11, 'child_id' => 24],
            ['parent_id' => 12, 'child_id' => 13],
            ['parent_id' => 12, 'child_id' => 14],
            ['parent_id' => 13, 'child_id' => 15],
            ['parent_id' => 14, 'child_id' => 15],
            ['parent_id' => 18, 'child_id' => 19],
            ['parent_id' => 18, 'child_id' => 20],
            ['parent_id' => 18, 'child_id' => 21],
            ['parent_id' => 19, 'child_id' => 27],
            ['parent_id' => 19, 'child_id' => 28],
            ['parent_id' => 20, 'child_id' => 27],
            ['parent_id' => 20, 'child_id' => 28],
            ['parent_id' => 21, 'child_id' => 27],
            ['parent_id' => 21, 'child_id' => 28],
            ['parent_id' => 22, 'child_id' => 25],
            ['parent_id' => 22, 'child_id' => 26],
            ['parent_id' => 23, 'child_id' => 25],
            ['parent_id' => 23, 'child_id' => 26],
            ['parent_id' => 24, 'child_id' => 25],
            ['parent_id' => 24, 'child_id' => 26],
            ];


    }
}
