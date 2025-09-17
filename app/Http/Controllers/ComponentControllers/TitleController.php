<?php

namespace App\Http\Controllers\ComponentControllers;

use App\Http\Controllers\Controller;
use App\Models\Components\Component;
use App\Models\Components\Page;
use App\Models\Components\Title;
use Illuminate\Http\Request;

class TitleController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request-> validate([
            'body' => 'required|string',
            'page_id'=>'required|integer|exists:pages,id',
            'order'=>'required|integer'
        ]);

        $page = Page::find($request->page_id);

        if ($page->is_question){
            return response()->json(['message' => 'Cant add component to question page'], 422);
        }

        $component=Component::create([
                'page_id' => $request->page_id,
                'order' => $request->order,
                'type' => 'title'
            ]
        );
        ComponentController::order($component,'add',null);

        $title = Title::create([
            'component_id' => $component->id,
            'body'=> $request->body,
        ]);
        $title->save();
        return response()->json(['message' => 'title created successfully',"title"=> $title->component], 201);
    }


    /**
     * Update the specified resource in storage.
     */
    public static function update(Request $request,$component)
    {
        //
        $request-> validate([
            'body' => 'string',
            'order' => 'integer'
        ]);
        if ($request->has('page_id')) {
            return response()->json(['error' => "can't change parent from this request"],401);
        }
        $title = $component->title;
        if ($request->has('order')) {
            ComponentController::order($component,'update',$request->order);
            $title->component->order =$request->order;
        }
            $title->update($request->all());
            $component->update($request->all());

        return response()->json(['message'=>"title updated successfully"]);
    }


}
