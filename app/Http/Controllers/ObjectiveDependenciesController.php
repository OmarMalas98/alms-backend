<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ObjectiveDependenciesController extends Controller
{
    public function addDependency(Request $request)
    {
        $request->validate([
            'objective_id' => 'required|exists:learning_objectives,id',
            'parent_objective_id' => 'required|exists:learning_objectives,id',
        ]);

        $objective = LearningObjective::find($request->objective_id);
        $parentObjective = LearningObjective::find($request->parent_objective_id);

        // Validation 1: Check if the parent objective is from the same zone and not a child of the objective.
        if ($objective->zone_id !== $parentObjective->zone_id) {
            return response()->json(['message' => 'Cannot link objectives from different zones.'], 400);
        }

        // Validation 2: Check if the parent objective is not already a child of the objective.
        if ($objective->parents->contains('id', $request->parent_objective_id)) {
            return response()->json(['message' => 'Cannot add a parent objective that is already a child.'], 400);
        }

        $objective->parents()->attach($request->parent_objective_id);

        return response()->json(['message' => 'Objective dependency added successfully.'], 200);
    }
}
