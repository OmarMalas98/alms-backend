<?php

namespace App\Http\Controllers\ComponentControllers;

use App\Http\Controllers\Controller;
use App\Models\Components\Component;
use App\Models\Components\Page;
use App\Models\Components\Video;
use App\Rules\ComponentOrderValidationRule;
use Illuminate\Http\Request;

class VideoController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request-> validate([
            'url' => 'required|string',
            'page_id'=>'required|integer|exists:pages,id',
            'order'=>['required','integer',new ComponentOrderValidationRule($request->page_id,'add')]
        ]);

        $page = Page::find($request->page_id);

        if ($page->is_question){
            return response()->json(['message' => 'Cant add component to question page'], 422);
        }

        $component=Component::create([
            'page_id' => $request->page_id,
            'order' => $request->order,
            'type' => 'video'
            ]
        );
        ComponentController::order($component,'add',null);

        $video = Video::create([
            'component_id' => $component->id,
            'url'=> $request->url
        ]);
        $video->save();
        return response()->json(['message' => 'Video created successfully',"video" => $video->component], 201);

    }



    /**
     * Update the specified resource in storage.
     */
    public static function update(Request $request, $component)
    {
        $video = $component->video;

        $request-> validate([
            'url' => 'string',
            'order' => ['integer',new ComponentOrderValidationRule($video->component->page_id,'update')]
        ]);
        if ($request->has('page_id')) {
            return response()->json(['error' => "can't change parent from this request"],401);
        }
        if ($request->has('order')) {
            ComponentController::order($component,'update',$request->order);
            $video->component->order =$request->order;
        }
        $video->update($request->all());
        $component->update($request->all());

        return response()->json(['message'=>"video updated successfully"]);

    }
}
