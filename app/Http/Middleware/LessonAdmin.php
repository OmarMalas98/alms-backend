<?php

namespace App\Http\Middleware;

use App\Models\Components\Page;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LessonAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lesson = Page::findOrFail($request->route('id'))->lesson;
        $parent = $lesson->content->parentCourse();;
        $admins = $parent->course->admins;
        if (!$admins->contains(auth()->user())) {
            return response()->json(['message' => 'unauthorized to get content'],401);
        }
        return $next($request);
    }
}
