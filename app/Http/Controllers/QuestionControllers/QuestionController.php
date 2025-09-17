<?php

namespace App\Http\Controllers\QuestionControllers;

use App\Http\Controllers\ComponentControllers\ComponentController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\QuestionControllers\Blank\BlankQuestionController;
use App\Http\Controllers\QuestionControllers\Cross\CrossQuestionController;
use App\Http\Controllers\QuestionControllers\MultiChoice\MultiChoiceQuestionController;
use App\Http\Controllers\ReorderingQuestionController;
use App\Models\Checkpoint;
use App\Models\Components\Component;
use App\Models\Components\Page;
use App\Models\Components\Question\Question;
use App\Models\Content\Assessment;
use App\Models\QuestionAttempt;
use App\Models\ReorderingQuestion;
use App\Models\User;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $questions = Assessment::find($request->route('id'))->questions;
        foreach ($questions as $question){
            switch ($question->type){
                case 'multi-choice':
                    $question = $question->multiChoiceQuestion;
                    break;
                case 'cross-question':
                    $question = $question->crossQuestion;
                    break;
                case 'reordering-question':
                    $question = $question->reorderQuestion;
                    break;
            }
        }
        return response()->json(['questions'=>$questions]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public static function store(Request $request, $type)
    {
        return DB::transaction(function () use ($request, $type) {
            $request->validate([
                'parent_id' => 'integer|exists:pages,id',
            ]);
            $page = Page::find($request->parent_id);
            if (!$page->components->isEmpty()) {
                abort(400,"can't add a question to a page that already has components");
            }
            $component = Component::create([
                'page_id' => $request->parent_id,
                'type' => 'question',
                'order' => $request->order
            ]);

            $question = Question::create([
                'type' => $type,
                'component_id' => $component->id
            ]);
            $page->is_question = 1;
            $page->save();
                ComponentController::order($question->component, 'add', $request->order);
            switch ($question->type) {
                case "multi-choice":
                    $response = MultiChoiceQuestionController::store($request, $question);
                    break;
                case 'blank-question':
                    $response=BlankQuestionController::store($request, $question);
                    break;
                case 'cross-question':
                    $response=CrossQuestionController::store($request, $question);
                    break;
                case 'reorder-question':
                    $response=ReorderingQuestionController::store($request, $question);
                    break;
            }

            return $response;
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Question $question)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public static function update(Request $request,$component)
    {
        return DB::transaction(function () use ($request,$component) {
            $question=$component->question;
            if (!$question)
            {
                return response()->json(['message' => "Question not found"], 404);

            }
            if ($request->has('order')) {
                ComponentController::order($question->component, 'update', $request->order);
            }

            switch ($question->type) {
                case "multi-choice":
                    $response = MultiChoiceQuestionController::update($request, $question);
                    break;
                case 'blank-question':
                    $response=BlankQuestionController::update($request, $question);
                    break;
                case 'cross-question':
                    $response=CrossQuestionController::update($request, $question);
                    break;
                case 'reorder-question':
                    $response=ReorderingQuestionController::update($request, $question);
                    break;
            }

            return $response;
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $question = Question::find($id);
        if (!$question) {
            return response()->json(['message' => "question not found"], 404);

        }
        $page = $question->component->page;
       ComponentController::order($question->component,'destroy',null);
        $question->delete();
        $page->is_question = 0;
        $page->save();
        return response()->json(['message' => "question deleted successfully"]);
    }

    public static function attempt(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'confidence_level' => 'required|integer|between:1,4',
        ]);

        $question = Question::find($request->question_id);
        $questionPageId = $question->component->page_id;
        $currentPageIds = Checkpoint::where('user_id', Auth()->user()->id)->pluck('page_id');
        if (!$currentPageIds->contains($questionPageId)) {
            $error='u cant attempt to this question';
            abort(404,$error);
        }

        switch ($question->type){
            case "multi-choice":
                $response=MultiChoiceQuestionController::attempt($request);
                 break;
            case 'blank-question':
                $response=BlankQuestionController::attempt($request);
                break;
            case 'cross-question':
                $response=CrossQuestionController::attempt($request);
                break;
            case 'reorder-question':
                $response=ReorderingQuestionController::attempt($request);
                break;
        }


        switch ($request->confidence_level) {
            case "1":
                $response *= 0;
                break;
            case "2":
                $response *= 0.5;
                break;
            case "3":
                $response *= 0.8;
                break;
            case "4":
                // No change to the response as the multiplier is 1.
                break;
            default:
                // Handle any other confidence_level not covered in the cases.
                // You might want to throw an exception or provide a default value.
                // For now, let's return the original response.
                break;
        }

            $pass = ($response >= 80) ? 1 : 0;
            QuestionAttempt::create([
                'user_id' => auth()->user()->id,
                'question_id' => $request->question_id,
                'learning_objective_id' => $question->component->page->learning_objective_id,
                'pass' => $pass
            ]);


        return $response;

    }
}
