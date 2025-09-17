<?php

namespace App\Http\Controllers\ContentControllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\VisitedController;
use App\Models\AchievedObjective;
use App\Models\AssessmentAttempt;
use App\Models\Content\Assessment;
use App\Models\Content\Content;
use App\Models\Question\MultiChoiceQuestion;
use App\Models\Visited;
use App\Rules\EnrolledInCourse;
use App\Rules\OrderValidationRule;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssessmentController extends Controller
{
    // /**
    //  * Display a listing of the resource.
    //  */
    // public function index()
    // {
    //     //
    //     $assessment = Assessment::with('status')->get();
    //     return response()->json(['assessments' => $assessment], 200);
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     //
    //     $request->validate([
    //         'title' => 'required|string|unique:courses,title',
    //         'description' => 'required|string',
    //         'order' => ['required', 'integer', new OrderValidationRule($request->parent_id, 'add')],
    //         'duration' => 'required',
    //         'status_id' => 'required|integer|exists:statuses,id',
    //         'parent_id' => 'required|integer|exists:contents,id',
    //         'learning_objective_id' => 'required|integer|exists:learning_objectives,id'
    //     ]);

    //     $parent = Content::find($request->parent_id);

    //     $content_type = $parent->content_type;
    //     if ($content_type == "lesson" || $content_type == "assessment") {
    //         return response()->json(['errors' => 'cannot add this content as parent'], 400);
    //     }
    //     $content = Content::create([
    //         'title' => $request->title,
    //         'content_type' => 'assessment',
    //         'parent_id' => $request->parent_id,
    //         'order' => $request->order,
    //     ]);
    //     ContentController::order($content, 'add', $request->order);

    //     if (Content::find($request->parent_id)->pluck('content_type') == 'lesson') {
    //         return response()->json(['error' => 'cant create assessment inside lesson'], 422);
    //     } else {
    //         $content->parent_id = $request->parent_id;
    //     }
    //     $duration = ContentController::convertTimeToMinutes($request->duration);
    //     $assessment = Assessment::create([
    //         'title' => $request->title,
    //         'description' => $request->description,
    //         'duration' => $duration,
    //         'status_id' => $request->status_id,
    //         'creator_id' => auth()->user()->id,
    //         'content_id' => $content->id,
    //         'learning_objective_id' => $request->learning_objective_id
    //     ]);
    //     $assessment->save();
    //     ContentController::addToDuration($duration, $content->parent_id);
    //     return response()->json(['message' => 'assessment created successfully', 'assessment' => $assessment], 201);
    // }

    // /**
    //  * Display the specified $resassessment
    //  */
    // public function show($id)
    // {
    //     //
    //     $assessment = Content::findOrFail($id)->assessment;
    //     if (!$assessment) {
    //         return response()->json(['message' => "assessment not found"], 404);
    //     }
    //     $questions = $assessment->questions;
    //     $questions->transform(function ($question) {
    //         return $question->getTypeRelation;
    //     });


    //     return response()->json([$questions]);
    // }

    // public static function update(Request $request, Content $content)
    // {
    //     //
    //     $request->validate([
    //         'title' => 'string|unique:courses,title',
    //         'description' => 'string',
    //         'points' => 'integer',
    //         'status_id' => 'integer|exists:statuses,id',
    //         'learning_objective_id' => 'required|integer|exists:learning_objectives,id'
    //     ]);
    //     $assessment = $content->assessment;
    //     if ($request->has('parent_id')) {
    //         // $content_type = Content::find($request->parent_id)->content_type;
    //         // if ($content_type == "lesson") {
    //         //     return response()->json(['errors' => 'cannot add lesson as parent'], 400);
    //         // }
    //         // $content->parent_id = $request->parent_id;
    //         return response()->json(['error' => "can't change parent from this request"], 401);
    //     }
    //     if ($request->has('order')) {
    //         ContentController::order($content, 'update', $request->order);
    //         $content->order = $request->order;
    //     }
    //     if ($request->has('title')) {
    //         $content->title = $request->title;
    //     }

    //     $content->save();
    //     if ($request->has('duration')) {
    //         $request->merge([
    //             'duration' => ContentController::convertTimeToMinutes($request->duration)
    //         ]);

    //         ContentController::addToDuration($request->duration, $assessment->content->parent_id);
    //     }
    //     $assessment->update($request->all());
    //     return response()->json(['message' => "assessment updated successfully"]);
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy($id)
    // {
    //     //
    //     $assessment = Assessment::find($id);
    //     if (!$assessment) {
    //         return response()->json(['message' => "assessment not found"], 404);
    //     }
    //     $status = ContentController::destroy($assessment->content)->status();
    //     if ($status == 200) {
    //         return response()->json(['message' => "assessment deleted successfully"]);
    //     } else {
    //         return response()->json(['message' => "Unauthorized"], 401);
    //     }
    // }

    // public function questions(Request $request)
    // {
    //     $assessment = Content::findOrFail($request->route('id'))->assessment;
    //     if (!$assessment) {
    //         return response()->json(['message' => "assessment not found"], 404);
    //     }
    //     $rule = new EnrolledInCourse($assessment->content->parentCourse()->id);
    //     if (!$rule->passes('', auth()->user()->id)) {
    //         return response()->json(['message' => "u didnt enroll the course of the assessment"], 203);
    //     }
    //     $visited = VisitedController::check($assessment->content_id);
    //     if (!$visited) {
    //         Visited::create([
    //             'user_id' => auth()->user()->id,
    //             'content_id' => $assessment->content_id,
    //             'course_id' => $assessment->content->parentCourse()->id,
    //             'finished' => 0
    //         ]);
    //     }
    //     $shuffledQuestions = [];
    //     $questions = $assessment->questions;

    //     foreach ($questions as $question) {
    //         if ($question->type == 'multi-choice') {
    //             $multiquestion = $question->multiChoiceQuestion;
    //             $options = $multiquestion->options->map(function ($option) {
    //                 return collect($option)->except('is_correct', 'updated_at', 'created_at')->toArray();
    //             })->toArray();
    //             shuffle($options);
    //             $shuffledMultiQuestion = new MultiChoiceQuestion();
    //             $shuffledMultiQuestion->id = $multiquestion->id;
    //             $shuffledMultiQuestion->question_id = $multiquestion->question_id;
    //             $shuffledMultiQuestion->text = $multiquestion->text;
    //             $shuffledMultiQuestion->created_at = $multiquestion->created_at;
    //             $shuffledMultiQuestion->updated_at = $multiquestion->updated_at;
    //             $shuffledMultiQuestion->options = $options;
    //             $shuffledQuestions[] = $shuffledMultiQuestion;
    //         }
    //     }


    //     return response()->json(['questions' => $shuffledQuestions]);
    // }


    //     function attempt(Request $request, $contentId)
    //     {
    //         $validator = Validator::make($request->all(), [
    //             'answers' => 'required|array',
    //             'answers.*.question_id' => 'required|exists:questions,id',
    //             'answers.*.option_id' => 'required|exists:options,id',
    //         ]);
    //         if ($validator->fails()) {
    //             return response()->json(['message' => $validator->errors()], 400);
    //         }

    //         $content = Content::find($contentId);
    //         if (!$content) {
    //             return response()->json(['message' => 'Content not found!'], 404);
    //         }
    //         $rule = new EnrolledInCourse($content->parentCourse()->id);
    //         if (!$rule->passes('', auth()->user()->id)) {
    //             return response()->json(['message' => "u didnt enroll the course of the assessment"], 203);
    //         }
    //         $visited = VisitedController::check($contentId);
    //         if (!$visited)
    //             return response()->json(['message' => "u cant attempt to this assessment"], 203);
    //         $assessment = $content->assessment;
    //         if (!$assessment) {
    //             return response()->json(['message' => 'Content is not assessment!'], 400);
    //         }
    //         $answers = $request->input('answers', []);
    //         $totalPoints = 0;
    //         $earnedPoints = 0;
    //         $questionResults = [];
    //         // Get the question IDs for the assessment
    //         $assessmentQuestionIds = $assessment->questions->pluck('id')->toArray();
    //         // Check if all assessment questions are included in the request
    //         $requestQuestionIds = collect($answers)->pluck('question_id')->toArray();
    //         $missingQuestionIds = array_diff($assessmentQuestionIds, $requestQuestionIds);
    //         if (!empty($missingQuestionIds)) {
    //             return response()->json(['message' => 'Some assessment questions are missing'], 400);
    //         }
    //         foreach ($assessment->questions as $question) {
    //             $totalPoints += $question->points;
    //             // Retrieve the question's type relation
    //             $questionType = $question->type;
    //             // Check if the question type is multi-choice
    //             if ($questionType == 'multi-choice') {

    //                 // Retrieve the correct option for the multi-choice question
    //                 $mquestion = $question->multiChoiceQuestion;
    //                 $correctOption = $mquestion->correctOption;
    //                 // Check if an answer is provided for the current question
    //                 $options = $mquestion->options;
    //                 foreach ($answers as $ans) {
    //                     if ($ans['question_id'] == $question->id) {
    //                         $answer = $ans;
    //                         break;
    //                     }
    //                 }
    //                 if (!$answer) {
    //                     return response()->json(['message' => 'Option not assigned to this question'], 404);
    //                 }
    //                 $found = false;
    //                 foreach ($options as $option) {
    //                     if ($option->id == $answer['option_id']) {
    //                         $found = true;
    //                         break;
    //                     }
    //                 }

    //                 if (!$found) {
    //                     return response()->json(['message' => 'Option not assigned to this question'], 404);
    //                 }

    //                 $isCorrect = ($answer['option_id'] === $correctOption->id);
    //                 if ($isCorrect) {
    //                     // Add points to the total if the answer is correct
    //                     $earnedPoints = $earnedPoints + $question->points;
    //                 }
    //                 $questionResults[] = [
    //                     'question_id' => $question->id,
    //                     'correct' => $isCorrect,
    //                 ];
    //             }
    //             // Add more cases for other question types if needed
    //         }
    //         // Perform additional actions based on the total points earned
    //         $percentage = ($earnedPoints * 100) / $totalPoints;

    //         if ($percentage == 100) {
    //             $response = true;
    //             AchievedObjective::create([
    //                 'score' => $percentage,
    //                 'learning_objective_id' => $assessment->learningObjective->id,
    //                 'user_id' => auth()->user()->id,
    //             ]);

    //             $visited->update([
    //                 'finished' => 1
    //             ]);


    //         } else {
    //             $response = false;
    //         }

    //         AssessmentAttempt::create([
    //             'score' => $percentage,
    //             'assessment_id' => $assessment->id,
    //             'user_id' => auth()->user()->id,
    //             'pass' => $response
    //         ]);

    //         if (!$response) {
    //             //to edit
    //             VisitedController::update($assessment->learningObjective->lesson->content->id, false);
    //         }

    //         return response()->json(['earned_points' => $earnedPoints, 'total_points' => $totalPoints, 'percentage' => $percentage, 'pass' => $response, 'questions' => $questionResults]);
    //     }



}

