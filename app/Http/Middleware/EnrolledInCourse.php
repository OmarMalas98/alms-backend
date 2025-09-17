<?php

namespace App\Http\Middleware;

use App\Models\Content\Content;
use App\Models\Course;
use Closure;

class EnrolledInCourse
{
    public function handle($request, Closure $next)
    {
        $courseId = $request->route('id');
        $userId = auth()->user()->id;

        $course = Course::findOrFail($courseId);

        if (!$course){
            return response()->json(['message' => 'Course not found!'], 404);
        }

        // Check if the user is enrolled in the course
        if (!$course->enrolledUsers()->where('users.id', $userId)->exists()) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }

        return $next($request);
    }
}

