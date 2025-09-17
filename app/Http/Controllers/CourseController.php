<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Models\Zone;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{

    public function all()
    {
        $user = auth()->user();

        $courses = Course::select('id', 'title', 'description', 'level_id')
            ->with('level')
            ->whereDoesntHave('enrolledUsers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        // Calculate duration for each course
        foreach ($courses as $course) {
            $course->stars = $course->stars();
            $course->lessonsCount = $course->zones()->count();
        }

        // Hide the 'level_id' field for this request
        $courses->makeHidden('level_id');

        return response()->json(['courses' => $courses], 200);

    }

    public function enrolledCourses(Request $request)
    {
        $user = $request->user();

        // Get all enrolled courses for the user with level information.
        $enrolledCourses = $user->enrolledCourses()
            ->select('courses.id', 'title', 'description', 'level_id')
            ->with('level')
            ->get();

        // Add the percentage of objectives achieved for each course.
        $enrolledCourses = $enrolledCourses->map(function ($course) use ($user) {
            $course->makeHidden('pivot', 'level_id');

            // Get the course's objectives count and achieved objectives count.
            $objectivesCount = $course->zones->flatMap->learningObjectives->count();
            $achievedObjectivesCount = $course->zones->flatMap->achievedObjectives->count();
            // Calculate the percentage of objectives achieved.
            $percentageAchieved = $objectivesCount > 0 ? ($achievedObjectivesCount / $objectivesCount) * 100 : 0;

            // Add the percentage to the course object.
            $course->percentage_achieved = number_format($percentageAchieved, 2);

            // Calculate and add stars information for the course.
            $course->stars = $course->stars();

            return $course;
        });

        return response()->json(['enrolled_courses' => $enrolledCourses], 200);
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $user = Auth::user();

        // get all courses where the auth user is an admin
        $courses = Course::whereHas('admins', function ($query) use ($user) {
            $query->where('admin_id', $user->id);
        })->with('status')->get();

        return response()->json(['courses' => $courses], 200);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|unique:courses,title',
            'description' => 'required|string',
            'level_id' => 'required|integer|exists:levels,id',
            'status_id' => 'required|integer|exists:statuses,id',
        ]);

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'level_id' => $request->level_id,
            'status_id' => $request->status_id,
            'creator_id' => auth()->user()->id,
        ]);
        $course->admins()->attach(auth()->user()->id);
        return response()->json(['message' => 'Course created successfully', 'course' => $course], 201);
    }

    public function getLessons($id){
        $course = Course::findOrFail($id);
        $lessons = $course->zones;
        return response()->json(['lessons' => $lessons,], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'title' => 'string|unique:courses,title,' . $course->id,
            'description' => 'string',
            'level_id' => 'integer|exists:levels,id',
            'status_id' => 'integer|exists:statuses,id', // Fixed typo here
        ]);
        $course->update($request->all());
        return response()->json(['message' => "course updated successfully","course" => $course]);
    }

    public function show(int $id)
    {
        $course = Course::with('level','enrolledUsers')->find($id);

        if (!$course) {
            return response()->json(['message' => "course not found"], 404);
        }
        $course->stars=$course->stars();
        $course->reviews=$course->reviews();


        // Remove created_at and updated_at from level
             if ($course->level) {
               $course->level->makeHidden(['created_at', 'updated_at']);
           }

        // Modify enrolled users data
        foreach ($course->enrolledUsers as $user) {
            $user->makeHidden(['email_verified_at', 'role_id', 'created_at', 'updated_at', 'pivot']);
        }
        return response()->json(['course' => $course], 200);
    }


    public function showS(int $id)
    {
        $course = Course::with('level')->find($id);

        if (!$course) {
            return response()->json(['message' => "course not found"], 404);
        }
        $course->stars=$course->stars();
        $course->reviews=$course->reviews();

        $users = $course->enrolledUsers()->get();

        if ($users->contains(auth()->user())) {
            $course->enrolled = true;
        } else {
            $course->enrolled = false;
        }

        // Remove created_at and updated_at from level
        if ($course->level) {
           $course->level->makeHidden(['created_at', 'updated_at']);
        }

        return response()->json(['course' => $course], 200);
    }


    public function destroy(string $id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json(['message' => "course not found"], 404);
        }
        $course->delete();
        return response()->json(['message' => "course deleted successfully"]);

    }

    /**
     * Add user to admins
     */
    public function addToAdmins(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'integer|exists:users,id',

        ]);
        $course = Course::findOrfail($id);
        if ($course->admins->pluck('id')->contains($request->user_id)) {
            return response()->json(['message' => "user already an admin"]);
        }
        if (User::find($request->user_id)->role_id != 1) {
            return response()->json(['message' => "user is not an admin"], 401);
        }
        $course->admins()->attach($request->user_id);

        return response()->json(['message' => "user added as an admin"]);

    }

    public function enrollInCourse(Request $request, $id)
    {

        // Retrieve the authenticated user
        $user = Auth::user();

        $course = Course::find($id);

        if (!$course) {
            return response()->json([
                'message' => 'Course not found!',
            ], 404);
        }

        // Check if the user is already enrolled in the course
        if ($user->enrolledCourses()->where('course_id', $course->id)->exists()) {
            return response()->json([
                'message' => 'User is already enrolled in the course.',
            ], 409); // Conflict status code
        }

        // Enroll the user in the course
        $user->enrolledCourses()->attach($course);

//        VisitedController::add($course->id,$content->id);
//        VisitedController::update($content->id,true);

        // Return success response
        return response()->json([
            'message' => 'User enrolled in the course successfully.',
        ]);
    }

    function unenrollInCourse($id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json([
                'message' => 'Course not found!',
            ], 404);
        }
        auth()->user()->enrolledCourses()->detach($course);
//        VisitedController::destroy($course->content->id);
        return response()->json(["message" => "User unenrolled in the course successfully."]);
    }

    function getFinishedCourses()
    {
        $response = auth()->user()->finishedCourses()->get();
        return response()->json(['courses' => $response]);
    }

    public function generateObjectivesGraph($courseId): \Illuminate\Http\JsonResponse
    {

        // Fetch the course by its ID
        $course = Course::findOrFail($courseId);

        // Initialize arrays to hold the nodes and arcs data
        $nodes = [];
        $arcs = [];

        // Function to recursively build the nodes and arcs for the learning objectives
        $buildGraph = function ($objective) use (&$nodes, &$arcs, &$buildGraph) {
            // Check if the node already exists in the nodes array
            $status = 'Disabled';
            if ($objective->available()) {
                if ($objective->achievedObjectives->contains('user_id', auth()->user()->id)) {
                    $status = 'Achieved';
                } else {
                    $status = 'Available';
                }
            }
            $node = [
                'id' => $objective->id,
                'name' => $objective->name,
                'zone_id' => $objective->zone->id,
                'status' => $status
            ];

            if (!in_array($node, $nodes)) {
                $nodes[] = $node;
            }

            // Get the child objectives (dependencies) for this objective

            $children = $objective->children;
            foreach ($children as $child) {
                $arc = [
                    'from' => $objective->id,
                    'to' => $child->id,
                ];

                // Check if the arc already exists in the arcs array
                if (!in_array($arc, $arcs)) {
                    $arcs[] = $arc;
                }

                // Recursively call buildGraph for the child objective
                $buildGraph($child);
            }
        };

        // Get the learning objectives for all the zones in the course
        $zones = $course->zones;
        foreach ($zones as $zone) {
            $objectives = $zone->learningObjectives;
            foreach ($objectives as $objective) {
                // Build the graph for each learning objective
                $buildGraph($objective);
            }
        }

        // Return the nodes and arcs as a JSON response
        return response()->json([
            'nodes' => $nodes,
            'arcs' => $arcs,
        ]);
    }


    function generateZonesGraph($courseId)
    {
        $course = Course::findOrFail($courseId);

        $zones = $course->zones;
        $nodes = [];
        $arcs = [];
        $addedArcs = [];
        // Step 1: Build nodes array
        foreach ($zones as $zone) {
            $nodes[] = [
                'id' => $zone->id,
                'title' => $zone->title,
                'description' => $zone->description,
                'status' => $zone->status(),
                'level' => $zone->level,
                'Achieved Objectives' => $zone->getAchievedObjectivesAttribute()->count(),
                'Lesson Objectives' => $zone->learningObjectives()->count(),
                'Need Self Assessment' => $zone->selfAssessments()->where('user_id', auth()->id())->get()->isEmpty()
                // Add any other properties you want to include for each zone
            ];
        }

        // Step 2: Build arcs array
        foreach ($zones as $zone) {
            $children = $zone->learningObjectives->flatMap(function ($objective) {
                return $objective->children->pluck('zone_id')->unique();
            });

            foreach ($children as $childZoneId) {
                if ($childZoneId !== $zone->id && !in_array($childZoneId . '-' . $zone->id, $addedArcs)) {
                    $arcs[] = [
                        'from' => $zone->id,
                        'to' => $childZoneId,
                    ];
                    // Add the reverse arc to avoid duplicates
                    $addedArcs[] = $childZoneId . '-' . $zone->id;
                }
            }
        }

        // Group nodes by level
        $groupedNodes = [];
        foreach ($nodes as $node) {
            $level = $node['level'];
            if (!isset($groupedNodes[$level])) {
                $groupedNodes[$level] = [];
            }
            $groupedNodes[$level][] = $node;
        }

        // Sort the groups by level
        ksort($groupedNodes);

        return [
            'nodes' => $groupedNodes,
            'arcs' => $arcs,
        ];
    }


}
