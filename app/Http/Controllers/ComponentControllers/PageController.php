<?php

namespace App\Http\Controllers\ComponentControllers;

use App\Http\Controllers\Controller;
use App\Models\Components\Page;
use App\Models\LearningObjective;
use App\Models\Zone;
use App\Rules\EnrolledInCourse;
use App\Rules\ExplanationLevelRule;
use App\Rules\OrderValidationRule;
use App\Rules\PageOrderVaildationRule;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use stdClass;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function store(Request $request)
    {
        $request->validate([
            'explanation_level' => 'required|integer',
            'order' => [
                'required',
                'integer',
                new OrderValidationRule('add',$request->learning_objective_id,$request->explanation_level),
                new ExplanationLevelRule('add',$request)
                // function ($attribute, $value, $fail) use ($request) {
                //     $existingPage = Page::where('learning_objective_id', $request->learning_objective_id)
                //         ->where('order', $value)
                //         ->first();

                //     if ($existingPage) {
                //         $fail("The order must be unique for the same explanation level and learning objective id.");
                //     }
                // }
            ],
            'learning_objective_id' => ['required','integer','exists:learning_objectives,id',Rule::in(collect(LearningObjective::find($request->learning_objective_id)->zone->getAllowedObjectives($request->order)))]
        ]);
        $page = Page::create([
            'explanation_level' => $request->explanation_level,
            'learning_objective_id' => $request->learning_objective_id,
            'order' => $request->order,
            'is_question' => 0
        ]);
        PageController::order($page,'add',$request->order);
        return response()->json(['message' => 'Page created successfully', 'page' => $page], 201);
    }





    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        $page = Page::find($id);
        if (!$page) {
            return response()->json(['message' => "page not found"], 404);
        }
        return response()->json(['page' => $page]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $page = Page::find($id);
        if (!$page)
        {
            return response()->json(['message' => "page not found"], 404);
        }
        $request->validate([
            'order' => [
                'integer',
                function ($attribute, $value, $fail) use ($request,$page) {
                    $existingPage = Page::where('learning_objective_id', $page->learning_objective_id)
                        ->where('order', $value)
                        ->first();

                    if ($existingPage) {
                        $fail("The order must be unique for the same explanation level and learning objective id.");
                    }
                },
                new ExplanationLevelRule('update',$request)
            ],
        ]);
        $page->update([
            'order'=>$request->order
        ]);

        return response()->json(['message' => 'Page updated successfully', 'page' => $page], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $page = Page::find($id);
        if (!$page) {
            return response()->json(['message' => "page not found"], 404);
        }
        $page->delete();
        return response()->json(['message' => "page deleted successfully"]);
    }
    // public function pagesOfLesson(Request $request, $id)
    // {

    //     $content = Content::findOrFail($id);

    //     $lesson = $content->lesson;
    //     if (!$lesson) {
    //         return response()->json(['message' => "lesson not found"], 404);
    //     }

    //     $request->validate([
    //         'explanation_level' => 'required|string|in:simple,medium,more explanation'
    //     ]);

    //     $rule = new EnrolledInCourse($content->parentCourse()->id);
    //     if (!$rule->passes('', auth()->user()->id)) {
    //         return response()->json(['message' => "u didnt enroll the course of the lesson"], 203);
    //     }

    //     $explanationLevel = ExplanationLevel::where('lesson_id', $lesson->id)
    //         ->where('level', $request->explanation_level)
    //         ->first();

    //     if (!$explanationLevel) {
    //         return response()->json(['error' => 'Invalid explanation level for the lesson'], 422);
    //     }

    //     $pages = Page::where('lesson_id', $lesson->id)
    //         ->where('explanation_level_id', $explanationLevel->id)
    //         ->orderBy('order')
    //         ->paginate(1);

    //     if ($pages->isEmpty()) {
    //         return response()->json(['message' => 'lesson has no pages'], 404);
    //     }

    //     if ($request->query->count()==2) {
    //         $page = $pages->filter(function ($child) use ($request) {
    //             return $child->order == $request->query->get('page');
    //         });
    //         if ($page->isEmpty()) {
    //             return response()->json(['message' => 'page not found'], 404);
    //         }
    //     }

    //     $pages->load('components');

    //     $pages->getCollection()->each(function ($page) {
    //         $page->components->transform(function ($component) {
    //             $typeRelation = $component->getTypeRelation();
    //             if ($typeRelation) {
    //                 $transformedComponent = new stdClass(); // Create a new object
    //                 $transformedComponent->type = $component->type; // Assign the 'type' property
    //                 $transformedComponent->component = $typeRelation->getResults(); // Assign the relation results

    //                 return $transformedComponent; // Return the new object
    //             }
    //             return $component;
    //         });
    //     });


    //     // Append the explanation level to the pagination links
    //     $pages->appends(['explanation_level' => $request->explanation_level]);

    //     return response()->json([$pages]);
    // }
    public static function order(Page $page, $method, $new_order)
    {
        if ($method == 'destroy') {
            $siblings = Page::where('explanation_level', $page->explanation_level)->where('learning_objective_id',$page->learning_objective_id)->get();
            $siblings = $siblings->filter(function ($child) use ($page) {
                return $page->order < $child->order;
            });
            foreach ($siblings as $sibling) {
                $sibling->order = $sibling->order - 1;
                $sibling->save();
            }
        } else if ($method == 'add') {
            $siblings = Page::where('explanation_level', $page->explanation_level)->where('learning_objective_id',$page->learning_objective_id)->get();
            $siblings = $siblings->filter(function ($child) use ($page) {
                return $page->order <= $child->order && $page->id != $child->id;
            });
            foreach ($siblings as $sibling) {
                $sibling->order = $sibling->order + 1;
                $sibling->save();
            }
        } else if ($method == 'update') {
            if ($new_order < $page->order) {
                $siblings = Page::where('explanation_level', $page->explanation_level)->where('learning_objective_id',$page->learning_objective_id)->get();
                $siblings = $siblings->filter(function ($child) use ($page, $new_order) {
                    return $child->order >= $new_order && $child->order < $page->order;
                });
                foreach ($siblings as $sibling) {
                    $sibling->order = $sibling->order + 1;
                    $sibling->save();
                }
            } else {
                $siblings = Page::where('explanation_level', $page->explanation_level)->where('learning_objective_id',$page->learning_objective_id)->get();
                $siblings = $siblings->filter(function ($child) use ($page, $new_order) {
                    return $child->order <= $new_order && $child->order > $page->order;
                });
                foreach ($siblings as $sibling) {
                    $sibling->order = $sibling->order - 1;
                    $sibling->save();
                }
            }
        }
    }
}
