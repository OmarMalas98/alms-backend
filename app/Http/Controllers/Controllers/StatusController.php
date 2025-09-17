<?php

namespace App\Http\Controllers\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Other\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $status = Status::all();
        return response()->json(['status' => $status], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name' => 'required|string|unique:statuses,name',
        ]);

        $status = new Status();
        $status->name = $request->name;
        $status->save();

        return response()->json(['message' => 'status created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $status = Status::find($id);

        if (!$status) {
            return response()->json(['message' => 'status not found'], 404);
        }

        return response()->json(['status' => $status], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            'name' => 'required|string|unique:statuses,name,' . $id,
        ]);

        $status = Status::find($id);

        if (!$status) {
            return response()->json(['message' => 'status not found'], 404);
        }

        $status->name = $request->name;
        $status->save();

        return response()->json(['message' => 'status updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $status = Status::find($id);

        if (!$status) {
            return response()->json(['message' => 'status not found'], 404);
        }

        $status->delete();

        return response()->json(['message' => 'status deleted successfully'], 200);
    }
}
