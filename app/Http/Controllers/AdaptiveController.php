<?php

namespace App\Http\Controllers;

use App\Models\AchievedObjective;
use App\Models\Content\Assessment;
use App\Models\Content\Content;
use App\Models\Content\Lesson;
use App\Models\LearningObjective;
use App\Models\Visited;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdaptiveController extends Controller
{
    public function getNextObjectiveLesson($id)
    {

        $content = Content::findOrFail($id);
        if($content->content_type != 'course'){
            return response()->json([
                'message' => 'Content is not a course! .',
            ],422);
        }
        $course = $content->course;

        // Retrieve the authenticated user
        $user = Auth::user();

        // Get the objectives that the user has not achieved yet
        $unachievedObjectives = LearningObjective::where('course_id',$course->id)->whereDoesntHave('achievedObjectives', function ($query) use ($course, $user) {
            $query->where('user_id', $user->id);
        })->get();


        // Check if there are unachieved objectives
        if ($unachievedObjectives->isEmpty()) {
            // All objectives have been achieved
            $user->enrolledCourses()->detach($course);
            $user->finishedCourses()->attach($course);
            return response()->json([
                'type' => 'end',
                'message' => 'All objectives have been achieved.',
            ]);
        }


        // Get the learning objective ID
        $learningObjectiveId = $unachievedObjectives->first()->id;

        // Retrieve the count of the user's previous attempts for the specific objective
        $previousAttemptsCount = $user->assessmentAttempts()
            ->whereHas('assessment', function ($query) use ($learningObjectiveId) {
                $query->where('learning_objective_id', $learningObjectiveId);
            })
            ->count();

        // Determine the lesson explanation level based on the count of previous attempts
        $lessonExplanationLevel = 'simple'; // Default level

        if ($previousAttemptsCount === 1) {
            $lessonExplanationLevel = 'medium';
        } elseif ($previousAttemptsCount > 1) {
            $lessonExplanationLevel = 'more explanation';
        }

        // Get the lesson based on the objective and explanation level
        $lesson = Lesson::where('learning_objective_id', $learningObjectiveId)
            ->first();

        $lesson->makeHidden('content');
        $lesson->parent = $lesson->content->parent;

        $contentOfLesson = $lesson->content;
        $visited = VisitedController::check($contentOfLesson->id);
        if ($visited == null){
            $visited = VisitedController::add($contentOfLesson->parentCourse()->id,$contentOfLesson->id);
        }

        if (!$visited->finished){
            $lessonArray = $lesson->toArray();
            $lessonArray = ['content_id' => $lesson->content->id] + $lessonArray;

            return response()->json([
                'type' => 'Lesson',
                'lesson Explanation Level' => $lessonExplanationLevel,
                'lesson' => $lessonArray,
            ]);
        } else{
            // Get the assessment associated with the objective
            $assessment = Assessment::where('learning_objective_id', $learningObjectiveId)
                ->first();

            if (!$assessment){
                AchievedObjective::create([
                    'score' => 100,
                    'learning_objective_id' => $learningObjectiveId,
                    'user_id' => auth()->user()->id,
                ]);
                return $this->getNextObjectiveLesson($id);
            }

            return response()->json([
                'type' => 'Assessment',
                'assessment' => $assessment,
            ]);
        }
    }

}
