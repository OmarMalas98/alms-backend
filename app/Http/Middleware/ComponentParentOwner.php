<?php

namespace App\Http\Middleware;

use App\Models\Components\Component;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ComponentParentOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $component = Component::find($request->route('id'));
        if (!$component) {
            return response()->json(["component not found"], 404);
        }
        $course = $component->page->learning_objective->zone->course;
        if (!$course->admins->contains(auth()->user())){
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
