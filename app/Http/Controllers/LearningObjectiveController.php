<?php

namespace App\Http\Controllers;

use App\Http\Controllers\QuestionControllers\Blank\BlankQuestionController;
use App\Http\Controllers\QuestionControllers\Cross\CrossQuestionController;
use App\Http\Controllers\QuestionControllers\MultiChoice\MultiChoiceQuestionController;
use App\Models\AchievedObjective;
use App\Models\Components\Component;
use App\Models\LearningObjective;
use App\Models\ObjectivesDependency;
use App\Models\Zone;
use App\Services\OpenAIService;
use Illuminate\Http\Request;

class LearningObjectiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $objectives=LearningObjective::all();
        return response()->json(['objectives' =>$objectives], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request-> validate([
            'name' => 'required|string',
            'zone_id'=> 'required|integer|exists:zones,id'
        ]);

        $objective=LearningObjective::create([
            'name'=>$request->name,
            'zone_id'=>$request->zone_id
            ]);
        if ($request->has('parent_id')) {
            LearningObjectiveController::addParent($request,$objective->id);
        }
        return response()->json(['message' => 'Learning Objective created successfully','objective'=>$objective], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $objective = LearningObjective::find($id);
        if (!$objective) {
            return response()->json(['message' => "objective not found"], 404);
        }
        return response()->json(['objective' => $objective], 200);

    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        //
        $objective = LearningObjective::find($id);
        if (!$objective) {
            return response()->json(['message' => "objective not found"], 404);
        }
        $request-> validate([
            'name' => 'string|unique:learning_objectives',
            'level' => 'string|unique:learning_objectives'
        ]);

        if ($request->has('parent_id')) {
            LearningObjectiveController::addParent($request,$objective->id);
        }
        $objective->update($request->all());

        return response()->json(['message'=>"objective updated successfully"]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $objective = LearningObjective::find($id);
        if (!$objective) {
            return response()->json(['message' => "objective not found"], 404);

        }
        $objective->linkChildrenWithParents();
        $objective->delete();
        return response()->json(['message' => "objective deleted successfully"]);
    }

    public function getNextObjectives(): array
    {
        $user = auth()->user();

        // Retrieve all objectives that the user has achieved
        $achievedObjectives = $user->achievedObjectives->pluck('learning_objective_id')->toArray();

        // Retrieve all objectives that have at least one parent
        $objectivesWithParents = LearningObjective::whereHas('parents')->get();

        $visitNodes = [];

        foreach ($objectivesWithParents as $objective) {
            $parents = $objective->parents->pluck('id')->toArray();

            // Check if the user has achieved all parents and hasn't achieved the current objective
            if (count(array_diff($parents, $achievedObjectives)) === 0 && !in_array($objective->id, $achievedObjectives)) {
                $visitNodes[] = $objective;
            }
        }

        // Retrieve objectives that do not have any parents
        $objectivesWithoutParents = LearningObjective::whereDoesntHave('parents')->get();

        foreach ($objectivesWithoutParents as $objective) {
            // Check if the user hasn't achieved the objective
            if (!in_array($objective->id, $achievedObjectives)) {
                $visitNodes[] = $objective;
            }
        }

        return $visitNodes;
    }

    public function availableParents($id){
        $learningObjective = LearningObjective::findOrFail($id);
        if ($learningObjective->zone->newZone() )
        {
            $allParents = LearningObjective::whereHas('zone.course', function ($query) use ($learningObjective) {
                $query->where('id', $learningObjective->zone->course_id);
            })
                ->where('id', '!=', $learningObjective->id)
                ->get();
        }
        else {
            $allParents = LearningObjective::whereHas('zone', function ($query) use ($learningObjective) {
                $query->where('course_id', $learningObjective->zone->course_id)
                    ->where('level', '=', $learningObjective->zone->level - 1)->orWhere('level', '=', $learningObjective->zone->level)->where('zone_id','=',$learningObjective->zone->id);
            })
                ->where('id', '!=', $learningObjective->id)
                ->get();
        }

        $parents = $allParents->reject(function ($parent) use ($learningObjective) {

            return ($learningObjective->isConnectedToObjective($parent->id) || $parent->isConnectedToObjective($learningObjective->id)) ;
        })->values()->makeHidden(['parents']);

        // Group the learning objectives by their zone_id
        $groupedParents = $parents->groupBy('zone_id');

        // Transform the grouped data into the desired format
        $groupedResponse = $groupedParents->map(function ($objectives, $zone_id) {
            $zone = Zone::find($zone_id); // Retrieve the Zone object
            $zone->objectives = $objectives; // Add objectives to the zone entity
            return $zone;
        })->values()->makeHidden(['created_at','updated_at']);

        return response()->json(['parents' => $groupedResponse], 200);
    }
    public function addParent(Request $request,$id)
    {
        $this->validate($request, [
            'parent_id' => 'required|exists:learning_objectives,id',
        ]);
        $objective = LearningObjective::find($id);
        if (!$objective)
            return response()->json(['message' => 'learning objective not found !!'], 404);
        $parentObjective = LearningObjective::find($request->parent_id);
        if (!$parentObjective)
            return response()->json(['message' => 'parent learning objective not found !!'], 404);

        $myZoneLevel=$objective->zone->level;
        $myParentZoneLevel=$parentObjective->zone->level;

        if ($myParentZoneLevel>$myZoneLevel && !$objective->zone->newZone())
        {
            abort(400,'Parent objective level must be bigger than or equal to the zone level by 1');
        }
        if ($objective->isConnectedToObjective($parentObjective->id))
        {
            return response()->json(['message' => 'Parent added successfully'], 200);
        }



        if ($objective->zone->id==$parentObjective->zone->id) {
            $objective->parents()->attach($request->parent_id);
        }

        else if (($myParentZoneLevel + 1 == $myZoneLevel ) || ($myZoneLevel == 1)) {
            if ($myZoneLevel==1)
                $objective->zone->update(['level'=>$myParentZoneLevel+1]);
            $objective->parents()->attach($request->parent_id);
        }



        $objective->getparent($parentObjective);
        return response()->json(['message' => 'Parent added successfully'], 200);
    }
    public function removeParent(Request $request,$id)
    {
        $dependency=ObjectivesDependency::where('parent_id',$request->parent_id)->where('objective_id',$id);
        if (!$dependency)
            return response()->json(['message' => "objective dependency not found"], 404);
        $dependency->delete();
        $course=LearningObjective::find($id)->zone->course;
        $course->refreshZonesLevel();
        return response()->json(['message' => 'Parent removed successfully'], 200);
    }

    public function generate($id, Request $request, OpenAIService $openai) {
        $request->validate([
            'type' => 'integer|min:1|max:4',
        ]);

        $objective = LearningObjective::findOrFail($id);
        switch ($request->type) {
            case '1':
                return MultiChoiceQuestionController::suggestNewFromObjective($objective,$openai);
            case '2':
                return BlankQuestionController::suggestNewFromObjective($objective,$openai);
            case '3':
                return CrossQuestionController::suggestNewFromObjective($objective,$openai);
            case '4':
                return ReorderingQuestionController::suggestNewFromObjective($objective,$openai);
        }
    }

}
