<?php

namespace App\Http\Controllers\ComponentControllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\QuestionControllers\Blank\BlankQuestionController;
use App\Http\Controllers\QuestionControllers\Cross\CrossQuestionController;
use App\Http\Controllers\QuestionControllers\MultiChoice\MultiChoiceQuestionController;
use App\Http\Controllers\QuestionControllers\QuestionController;
use App\Http\Controllers\ReorderingQuestionController;
use App\Models\BlankQuestion;
use App\Models\Components\Component;
use App\Models\Components\Page;
use App\Models\CrossQuestion;
use App\Models\LearningObjective;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ComponentController extends Controller
{
    public function index(Request $request){
        $page = Page::find($request->route('id'));
        if (!$page) {
            return  response()->json(['message' => "page not found"], 404);
        }
        $course = $page->lesson->content->parentCourse()->course;

        if (!$course->admins->contains(auth()->user())) {
            return response()->json(['message' => "You are not authorized to view this page"], 403);
        }
        $components = $page->components;
        foreach ($components as $component){
            switch ($component->type){
                case 'video':
                    $component = $component->video;
                    break;
                case 'textarea':
                    $component = $component->textarea;
                    break;
                case 'title':
                    $component = $component->title;
                    break;
            }
        }
        return response()->json(['components'=>$components]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $component = Component::find($request->route('id'));

            if (!$component) {
                return response()->json(['message' => "Component not found"], 404);
            }

            $parent_id = $component->page_id;

            $request->validate([
                'page_id' => 'integer|min:1|exists:pages,id',
                'order' => 'integer',
            ]);

            $new = Arr::except($component->getTypeRelation->getAttributes(), ['id', 'component_id', 'created_at', 'updated_at']);
            $newRequest = Arr::except($request->all(), ['page_id', 'order']);

            if ($component->type == 'question') {
                switch ($component->getTypeRelation->type) {
                    case 'multi-choice':
                    $new = Arr::except($component->getTypeRelation->getTypeRelation->getAttributes(), ['id', 'question_id', 'created_at', 'updated_at']);
                    $newRequest = Arr::except($request->all(), ['page_id', 'order','options']);
                    break;
                    case 'cross-question':
                        $new = Arr::except($component->getTypeRelation->getTypeRelation->getAttributes(), ['id', 'question_id', 'created_at', 'updated_at']);
                        $newRequest = Arr::except($request->all(), ['page_id', 'order','left_options','right_options']);
                        break;
                    case 'blank-question':
                        $new = Arr::except($component->getTypeRelation->getTypeRelation->getAttributes(), ['id', 'question_id', 'created_at', 'updated_at']);
                        $newRequest = Arr::except($request->all(), ['page_id', 'order','blanks']);
                    break;
                    case 'reorder-question':
                        $new = Arr::except($component->getTypeRelation->getTypeRelation->getAttributes(), ['id', 'question_id', 'created_at', 'updated_at']);
                        $newRequest = Arr::except($request->all(), ['page_id', 'order','items']);
                    break;
                }
            }
            if (array_diff_key($newRequest, $new)) {
                return response()->json(['Properties provided in body are for a different content type'], 403);
            }
            switch ($component->type) {
                case 'video':
                    return response()->json(VideoController::update($request, $component));
                case 'textarea':
                    return response()->json(TextAreaController::update($request, $component));
                case 'title':
                    return response()->json(TitleController::update($request, $component));
                case 'question':
                    return response()->json(QuestionController::update($request, $component));
            }
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public static function destroy($id)
    {
        $component = Component::find($id);
        if (!$component) {
            return response()->json(['message' => "component not found"], 404);

        }
        ComponentController::order($component,'destroy',null);

        if ($component->question){
            $component->page->is_question = 0;
            $component->page->save();
        }

        $component->delete();
        return response()->json(['message' => "component deleted successfully"]);
    }
    public static function moveComponent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_id' => 'required|integer|exists:pages,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        if ($request->page_id == $request->route('id')) {
            return  response()->json(['message' =>'Cant move content into itself'], 401);
        }
        $component = Component::find($request->route('id'));
        if (!$component){
            return response()->json(['message' =>'Component not found'], 404);
        }
        $component->order = $component->page->components->sortBy('order')->last()->order +1 ;
        $component->page_id=$request->page_id;
        $component->save();
        return response()->json(['message' => 'Component moved successfully']);
    }
    public static function order(Component $component,$method,$new_order)
    {
        if ($method == 'destroy') {
            $siblings = $component->page->components->filter(function ($child) use ($component) {
                return $component->order < $child->order;
            });
            foreach ($siblings as $sibling ) {
                $sibling->order = $sibling->order - 1;
                $sibling->save();
            }
        }
        else if ($method == 'add') {
            $siblings = $component->page->components->filter(function ($child) use ($component) {
                return $component->order <= $child->order && $component->id != $child->id;
            });
            foreach ($siblings as $sibling ) {
                $sibling->order = $sibling->order + 1;
                $sibling->save();
            }
        }else if ($method == 'update') {
            if($new_order < $component->order){
                $siblings = $component->page->components->filter(function ($child) use ($component,$new_order) {
                    return $child->order >= $new_order && $child->order < $component->order;
                });
                foreach ($siblings as $sibling ) {
                    $sibling->order = $sibling->order + 1;
                    $sibling->save();
                }
            }else{
                $siblings = $component->page->components->filter(function ($child) use ($component,$new_order) {
                    return $child->order <= $new_order && $child->order > $component->order;
                });

                foreach ($siblings as $sibling ) {
                    $sibling->order = $sibling->order - 1;
                    $sibling->save();
                }
            }
            $component->order = $new_order;
            $component->save();
        }

    }
    public function generate($id,OpenAIService $openai) {
        $question = Component::findOrFail($id)->question;
        switch ($question->type) {
        case 'multi-choice':
            return MultiChoiceQuestionController::suggestNewFromComponent($question,$openai);
        case 'blank-question':
            return BlankQuestionController::suggestNewFromComponent($question,$openai);
        case 'cross-question':
            return CrossQuestionController::suggestNewFromComponent($question,$openai);
        case 'reorder-question':
            return ReorderingQuestionController::suggestNewFromComponent($question,$openai);
       }
    }
    public function respondToGen(Request $request , $id) {

        $validatedData = $request->validate([
            'answer' => 'required|boolean'
        ]);

        $component = Component::findOrFail($id);
        if (!$component->is_suggested) {
          return response()->json(["message" => "component is not available for this type of request"]);
        }
        else{
            if ($request->answer == 1) {
                $component->is_suggested = false;
                $component->save();
                return response()->json(["message" => "component is now added successfully"]);
            }
            else{
                $page = $component->page;
                $page->delete();
                return response()->json(["message" => "component deleted successfully"]);
            }
        }
    }
}
