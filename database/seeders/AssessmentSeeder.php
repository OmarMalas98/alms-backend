<?php

namespace Database\Seeders;

use App\Http\Controllers\ContentControllers\ContentController;
use App\Models\Content\Assessment;
use App\Models\Content\Content;
use Illuminate\Database\Seeder;

class AssessmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $content = Content::create([
            'title' => 'Assessment1',
            'content_type' => 'assessment',
            'parent_id' => 3,
            'order' => 2,
        ]);

        $duration = 5;
        $assessment = Assessment::create([
            'title' => 'Assessment1',
            'description' => 'description',
            'duration'=> $duration,
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $content->id,
            'learning_objective_id'=>2
            ]);
        $assessment->save();
        ContentController::addToDuration($duration , $content->parent_id);
    }
}
