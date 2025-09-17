<?php

namespace App\Http\Controllers;

use App\Http\Controllers\QuestionControllers\QuestionController;
use App\Models\AchievedObjective;
use App\Models\AchievedZones;
use App\Models\Checkpoint;
use App\Models\Components\Page;
use App\Models\Components\Question\MultiChoice\MultiChoiceQuestion;
use App\Models\Course;
use App\Models\LearningObjective;
use App\Models\QuestionAttempt;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $zones = Zone::all();
        return response()->json(['zones' => $zones], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'course_id' => 'required|integer|exists:courses,id',
        ]);
        $course = Course::find($request->course_id);

        if ($course->creator_id != Auth()->user()->id)
            return response()->json(['message' => 'u dont have permission to add zone to this course'], 201);

        $zone = Zone::create([
            'title' => $request->title,
            'description' => $request->description,
            'course_id' => $request->course_id
        ]);


        return response()->json(['message' => 'Zone created successfully', 'lesson' => $zone], 201);
    }

    public function show($id)
    {
        $zone = Zone::find($id);

        if (!$zone) {
            return response()->json(['message' => "Zone not found"], 404);
        }

        $zone->learningObjectives->transform(function ($learningObjective) {
            unset($learningObjective->achieved_objectives);
            return $learningObjective;
        });

        $zone->unachieved_objectives = $zone->objectivesNotAchievedByUser();

        return response()->json(['lesson' => $zone], 200);
    }



    public function objectivesOfZone($id){

        $zone = Zone::find($id);
        if (!$zone) {
            return response()->json(['message' => "Zone not found"], 404);
        }
        $learningObjectives=$zone->learningObjectivesWithChildren;
        return response()->json(['learning_objectives' => $learningObjectives], 200);
    }


    /**
     * Display the specified resource.
     */
    public function pagesOfZone($id)
    {
        //
        $zone = Zone::find($id);
        if (!$zone) {
            return response()->json(['message' => "Zone not found"], 404);
        }
        // if ($zone->status() == 'Unavailable')
        //     return response()->json(['message' => "Cant get into this lesson"], 403);
        $pages = $zone->pages();
        return response()->json(['Pages' => $pages]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $zone = Zone::find($id);
        if (!$zone) {
            return response()->json(['message' => "zone not found"], 404);
        }
        if ($zone->course->creator_id != Auth()->user()->id)
            return response()->json(['message' => 'u dont have permission to add zone to this course'], 201);

        $request->validate([
            'name' => 'string|unique:zones',
            'descripton' => 'string:learning_objectives'
        ]);

        $zone->update($request->all());

        return response()->json(['message' => "Zone updated successfully"]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $zone = Zone::find($id);
        if (!$zone) {
            return response()->json(['message' => "lesson not found"], 404);

        }
        $zone->deleteWithDependencies();
        $zone->delete();

        return response()->json(['message' => "lesson deleted successfully"]);
    }

    public function current($id)
    {
        $zone = Zone::find($id);

        if (!$zone)
            return response()->json(['message' => "lesson not found"], 404);


        if (!$zone->mySelfAssessment())
        {
            return response()->json(['message' => "u didnt choose self assessment for this lesson."], 422);
        }
        $checkPoint = $zone->getUserCheckpoint();

        if (!$checkPoint) {
            $nextObjective = $zone->next();

            switch ($zone->mySelfAssessment()->first()->rating){
                case 1:
                    $nextPage = $nextObjective->firstPage(false);
                    break;
                case 2:
                    $nextPage = $nextObjective->firstPage(true);
                    break;
                case 3:
                    $nextPage = $nextObjective->firstQuestion();
                    break;
                case 4:
                    $nextPage = $nextObjective->firstQuestion();
                    break;
                case 5:
                    $nextPage = $nextObjective->firstQuestion();
                    break;

            }
            Checkpoint::create(
                [
                    'user_id' => Auth::user()->id,
                    'zone_id' => $id,
                    'page_id' => $nextPage->id
                ]
            );
            return response()->json([
                'page' => $nextPage,
                'Achieved Objectives' => $zone->getAchievedObjectivesAttribute()->count(),
                'Lesson Objectives' => $zone->learningObjectives()->count()
            ], 200);
        }

        $page = Page::find($checkPoint->page_id);
        $objective = $page->learning_objective->achievedObjectives->where('user_id', Auth()->user()->id)->where('score', '>', 75)->first();
        if ($objective) {
            return response()->json([
                'message' => 'End of the lesson',
                'Achieved Objectives' => $zone->getAchievedObjectivesAttribute()->count(),
                'Lesson Objectives' => $zone->learningObjectives()->count()
            ], 200);
        }
        $page = $page->makeHidden(['learning_objective']);
        $page->components->each(function ($component) {
            switch ($component->type) {
                case 'video':
                    $component->load('video');
                    $component->video->makeHidden(['component_id', 'created_at', 'updated_at']);
                    break;
                case 'textarea':
                    $component->load('textarea');
                    $component->textarea->makeHidden(['component_id', 'created_at', 'updated_at']);
                    break;
                case 'title':
                    $component->load('title');
                    $component->title->makeHidden(['component_id', 'created_at', 'updated_at']);
                    break;
                case 'question':
                    $component->load('question');
                    $question = $component->question->makeHidden(['id', 'component_id', 'created_at', 'updated_at']);
                    switch ($question->type){
                        case 'multi-choice':
                            $multiquestion = $question->multiChoiceQuestion;
                            $options = $multiquestion->options->map(function ($option) {
                                return collect($option)->except('is_correct', 'updated_at', 'created_at')->toArray();
                            })->toArray();
                            shuffle($options);
                            $shuffledMultiQuestion = new MultiChoiceQuestion();
                            $shuffledMultiQuestion->id = $multiquestion->id;
                            $shuffledMultiQuestion->question_id = $multiquestion->question_id;
                            $shuffledMultiQuestion->text = $multiquestion->text;
                            $shuffledMultiQuestion->options = $options;
                            $question->multiChoiceQuestion = $shuffledMultiQuestion;
                            break;
                        case 'blank-question':
                            $question->blankQuestion;
                            break;
                        case 'cross-question':
                            $crossQuestion = $question->crossQuestion;
                            $crossQuestion->right = Arr::shuffle($crossQuestion->rightOptions->toArray());
                            $crossQuestion->left = Arr::shuffle($crossQuestion->leftOptions->makeHidden('right_option_id')->toArray());
                            $crossQuestion->makeHidden('rightOptions');
                            $crossQuestion->makeHidden('leftOptions');
                            $question->crossQuestion = $crossQuestion;
                            break;
                        case 'reorder-question':
                            $reorderingQuestion=$question->reorderQuestion;
                            // Hide specific attributes from the "question" object
                            $reorderingQuestion->makeHidden(['id', 'created_at', 'updated_at']);
                            $shuffledItems = $reorderingQuestion->items->shuffle();
                            $shuffledItems->each(function ($item) {
                                $item->makeHidden(['reordering_question_id', 'created_at', 'updated_at']);
                            });
                            $reorderingQuestion->makeHidden('items');
                            $reorderingQuestion->lines = $shuffledItems;
                            break;
                    }
                    break;
                // Add more cases for other types if needed
            }
            unset($component['page_id']);
            unset($component['created_at']);
            unset($component['updated_at']);
        });
        return response()->json([
            'page' => $page,
            'Achieved Objectives' => $zone->getAchievedObjectivesAttribute()->count(),
            'Lesson Objectives' => $zone->learningObjectives()->count(),
            'self_assessment'=>$zone->mySelfAssessment()->first()->rating
        ], 200);
    }

    public function next($id, Request $request)
    {
        // Find the Zone based on the given ID.
        $zone = Zone::find($id);
        // Check if the Zone exists.
        if (!$zone)
            return response()->json(['message' => "Lesson not found"], 404);

        if (!$zone->available())
            return response()->json(['message' => "Lesson not available for you"], 403);

        // Get the user's checkpoint for the Zone.
        $checkPoint = $zone->getUserCheckpoint();

        // Start of the lesson (user doesn't have a checkpoint).
        if (!$checkPoint) {
            // Get the next objective for the Zone.
            $nextObjective = $zone->next();

            switch ($zone->mySelfAssessment()->first()->rating){
                case 1:
                    $nextPage = $nextObjective->firstPage(false);
                    break;
                case 2:
                    $nextPage = $nextObjective->firstPage(true);
                    break;
                case 3:
                    $nextPage = $nextObjective->firstQuestion();
                    break;
                case 4:
                    $nextPage = $nextObjective->firstQuestion();
                    break;
                case 5:
                    $nextPage = $nextObjective->firstQuestion();
                    break;
            }

            $checkPoint = new Checkpoint();
            // Create a new checkpoint for the user with the next page.
            Checkpoint::create([
                'user_id' => Auth::user()->id,
                'zone_id' => $id,
                'page_id' => $nextPage->id
            ]);
        } else {
            // User has a checkpoint, get the current page.
            $page = Page::find($checkPoint->page_id);

            // Get the learning objective from the current page.
            $learningObjective = $page->learning_objective;

            // If the current page is not a question page.
            if (!$page->is_question) {
                // Get the next page after the current page.
                $nextPage = $page->nextPage();

                // If there is no next page, the lesson ends.
                if (!$nextPage) {
                    $nextPage = $page->nextQuestion();
                    if (!$nextPage) {
                        $learningObjective->updateScoreForUser(100);

                        // Get the next page after the question.
                        $nextPage = $zone->next()->firstPage();
                    }
                }
            } else {
                // If the current page is a question page, attempt to answer the question.
                $response = QuestionController::attempt($request);

                $learningObjective->updateScoreForUser($response);

                if ($response < 75) {
                    $page->learning_objective->fail(5);
                }

                // Get the next page after the question.
                $nextObjective = $zone->next();
                if ($nextObjective){
                    switch ($zone->mySelfAssessment()->first()->rating){
                        case 1:
                            $nextPage = $nextObjective->firstPage(false);
                            break;
                        case 2:
                            $nextPage = $nextObjective->firstPage(true);
                            break;
                        case 3:
                            if ($nextObjective->id == $learningObjective->id){
                                $nextPage = $nextObjective->firstPage(true);
                            }
                            else{
                                $nextPage = $nextObjective->firstQuestion();
                            }
                            break;
                        case 4:
                            if ($nextObjective->id == $learningObjective->id){
                                $attemptsCount = QuestionAttempt::where('user_id', auth()->user()->id)
                                    ->where('learning_objective_id', $learningObjective->id)
                                    ->count();
                                if ($attemptsCount%3 != 0){
                                    $nextPage = $nextObjective->firstQuestion();
                                }
                                else{
                                    $nextPage = $nextObjective->firstPage(true);
                                }
                            }
                            else{
                                $nextPage = $nextObjective->firstQuestion();
                            }
                            break;
                        case 5:
                            $nextPage = $nextObjective->firstQuestion();
                            break;
                    }
                }
                else{
                    $nextPage = null;
                }

            }
        }
        // If there is no next page => end of lesson
        if (!$nextPage) {
            $achievedZone = $zone->achievedByUser();
            if (!$achievedZone){
                AchievedZones::create([
                    'zone_id' => $zone->id,
                    'user_id' => Auth::user()->id
                ]);
            }
            $zone->getUserCheckpoint()->delete();
            return response()->json([
                'message' => 'End of the lesson',
                'Achieved Objectives' => $zone->getAchievedObjectivesAttribute()->count(),
                'Lesson Objectives' => $zone->learningObjectives()->count(),
                'self_assessment'=>$zone->mySelfAssessment()->first()->rating
            ], 200);
        }

        // Update the user's checkpoint with the ID of the next page.
        $checkPoint->update([
            'page_id' => $nextPage->id
        ]);

        // Return the next page and information about achieved objectives and lesson objectives.
        return response()->json([
            'page' => $nextPage,
            'Achieved Objectives' => $zone->getAchievedObjectivesAttribute()->count(),
            'Lesson Objectives' => $zone->learningObjectives()->count(),
            'self_assessment'=>$zone->mySelfAssessment()->first()->rating
        ], 200);
    }


    public function resetProgress($id)
    {
        $zone = Zone::find($id);

        if (!$zone)
            return response()->json(['message' => "zone not found"], 404);
        $user = Auth()->user();
        $userId = $user->id;

        // Step 1: Remove all checkpoints for the user in this zone.
        $zone->checkpoints()->where('user_id', $userId)->delete();

        // Step 2: Get all learning objectives in this zone that are achieved by the user.
        $achievedObjectives = $zone->learningObjectives->filter(function ($objective) use ($userId) {
            return $objective->achievedObjectives->where('user_id', $userId)->isNotEmpty();
        });

        // Loop through each learning objective in the zone.
        foreach ($zone->learningObjectives as $objective) {
            // Delete the achieved objectives for the user and the specific learning objective.
            $user->achievedObjectives()->where('learning_objective_id', $objective->id)->delete();

            // Delete the question attempts for the user and the specific learning objective.
            $user->questionAttempts()->where('learning_objective_id', $objective->id)->delete();
        }

        // Return the JSON response with the success message.
        return response()->json([
            'message' => 'Student progress reset successfully.',
        ], 200);
    }
    public function showPages(Request $request,$id) {
        $zone = Zone::find($id);
        if (!$zone) {
            return response()->json(['message' => "Zone not found"], 404);
        }
        // if ($zone->status() == 'Unavailable')
        //     return response()->json(['message' => "Cant get into this lesson"], 403);
        $sortedPages = $zone->learningObjectives()
                  ->with(['pages' => function ($query) {
                      $query->orderBy('order')->withCount('components'); // Order pages within each learning objective
                  }])
                  ->get()
                  ->pluck('pages')
                  ->flatten();
        return response()->json($sortedPages);
    }
    public function getObjectives($id) {
        $objectives = Zone::findOrFail($id)->learningObjectives;
        return response()->json($objectives);
    }

    public function getObjectivesTree($id) {
        $objectives = Zone::findOrFail($id)->firstNodes()->load('parents.zone');
        $objectives->load('recursiveChildren');
        return response()->json(['learning_objectives'=>$objectives]);
    }

    public function submitSelfAssessment(Request $request, $id)
    {
        $zone = Zone::find($id);
        if (!$zone) {
            return response()->json(['message' => "Zone not found"], 404);
        }

        $this->validate($request, [
            'rating' => 'required|integer|between:1,5', // Assuming rating is an integer between 1 and 5.
        ]);

        $rating = $request->input('rating');
        $userId = auth()->id();

        // Check if the user has already rated the zone.
        $existingRating = $zone->selfAssessments()
            ->where('user_id', $userId)
            ->first();

        if ($existingRating) {
            // Update existing rating.
            $existingRating->rating = $rating;
            $existingRating->save();
        } else {
            // Create new rating.
            $ratingData = [
                'user_id' => $userId,
                'rating' => $rating,
            ];
            $zone->selfAssessments()->create($ratingData);
        }

        return response()->json(['message' => 'Self assessment submitted successfully']);
    }
    function getAvailableObjectives(Request $request, $id) {
        $objectivesId = Zone::find($id)->getAllowedObjectives($request->order);
        $response = array();
        foreach ($objectivesId as $id) {
            array_push($response,LearningObjective::find($id));
        }
        return response()->json(['objectives' => $response]);
    }
}
