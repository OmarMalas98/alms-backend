<?php

namespace App\Http\Controllers\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Other\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $response = Level::all();
        return response()->json(['levels' => $response], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:levels,name',
            'description'=> 'required|string'
        ]);

        $level = new Level();
        $level->name = $request->name;
        $level->description = $request->description;
        $level->save();

        return response()->json($level, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $level = Level::find($id);
        if (!$level) {
            return response()->json(['message' => 'Level not found'], 404);
        }

        return response()->json(['level' => $level], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'string|unique:levels,name',
            'description' => 'string'

        ]);

        $level = Level::find($id)->first();
        if (!$level) {
            return response('Invalid id', 404);
        }
        $level->update($request->all());
        $level->save();

        return response()->json(['message' => 'Level updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $level = Level::find($id)->first();
        if (!$level) {
            return response('Invalid id', 404);
        }
        $level->delete();

        return response()->json(['message' => 'Level deleted successfully'], 200);
    }
}
