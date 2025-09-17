<?php

namespace App\Http\Middleware;

use App\Models\Components\Component;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ComponentOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $component = Component::find($request->parent_id);
        if (!$component) {
            return response()->json(["parent component not found"], 404);
        }
        if (!$component->page->lesson->content->parentCourse()->course->admins->contains(auth()->user())){
            return response()->json(["Unauthorized"], 403);
        }
        return $next($request);
    }
}
