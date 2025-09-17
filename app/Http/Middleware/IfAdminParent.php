<?php

namespace App\Http\Middleware;

use App\Models\Content\Content;
use App\Models\Course;
use App\Models\LearningObjective;
use App\Models\Zone;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IfAdminParent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $zone = LearningObjective::findOrFail($request->learning_objective_id)->zone;
        $course = $zone->course;
        if (!$course) {
            return response()->json(["course not found"], 404);
        }

        $admins = $course->admins;
        if (!$admins->contains(auth()->user())) {
            return response()->json(['message' => 'unauthorized to get content'],401);
        }
        return $next($request);
    }
}
