<?php

namespace Database\Seeders;

use App\Models\Other\Level;
use Illuminate\Database\Seeder;

class LevelTableSeeder extends Seeder
{
    public function run()
    {
        // Add seed data for levels table
        $levels = [
            [
                'name' => 'Beginner',
                'description' => 'This level is for beginners who are new to the topic.'
            ],
            [
                'name' => 'Intermediate',
                'description' => 'This level is for people who have some experience with the topic.'
            ],
            [
                'name' => 'Advanced',
                'description' => 'This level is for experts who have a deep understanding of the topic.'
            ]
        ];

        foreach ($levels as $level) {
            Level::create($level);
        }
    }
}
