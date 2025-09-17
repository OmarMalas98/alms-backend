<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\CourseContent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminCourse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $course = Course::with('admins')->find($request->route('id'));
        if(!$course){
            return response()->json(['message' => 'Course Not Found!'], 404);        }
        $ids = $course->admins->pluck('id');
        if (auth()->user()->role_id == 1 && $ids->contains(auth()->user()->id)) {
            return $next($request);
        } else
            return response()->json(['message' => 'Not Allowed, Current user is not admin of given course'], 401);
    }
}
