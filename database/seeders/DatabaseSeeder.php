<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleTableSeeder::class);
        $this->call(StatusTableSeeder::class);
        $this->call(LevelTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        // $this->call(FinalSeeder::class);
        $this->call(LearningObjectiveSeeder::class);
//        $this->call(ModuleTableSeeder::class);
//        $this->call(LearningObjectiveSeeder::class);
//        $this->call(LessonTableSeeder::class);
//        $this->call(AssessmentSeeder::class);
//        $this->call(MultiChoiceQuestionTableSeeder::class);x
//        $this->call(PageSeeder::class);
//        $this->call(ComponentSeeder::class);
//        $this->call(EnglishCourseSeeder::class);
        // $this->call(EnglishCourse::class);

    }
}
