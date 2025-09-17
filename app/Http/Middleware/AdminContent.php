<?php

namespace App\Http\Middleware;

use App\Models\Content\Content;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminContent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $content = Content::find($request->route('id'));
        if (!$content) {
            return response()->json(["content not found"], 404);
        }
//        $request->attributes->set('content', $content);
        $parent = $content->parentCourse();
        if ($parent) {
            $content_course = $parent;
            $admins = $content_course->course->admins;
            if (!$admins->contains(auth()->user())) {
                return response()->json(['message' => 'unauthorized to get content'],401);
            }
        }
        else{
            $content_course = $content->course;
            $admins = $content_course->admins;
            if (!$admins->contains(auth()->user())) {
                return response()->json(['message' => 'unauthorized to get content'],401);
            }
        }
        return $next($request);

    }
}
