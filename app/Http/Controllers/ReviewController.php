<?php

namespace App\Http\Controllers;

use App\Models\Content\Content;
use App\Models\Course;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function create(Request $request,$id)
    {
        $validatedData = $request->validate([
            'star' => ['required', 'numeric', 'between:0,5', 'step:0.5'],
            'comment' => ['nullable', 'string'],
        ]);

        $course = Course::findOrFail($id);
        $userId = auth()->user()->id;

        // Check if the user has already reviewed the course
        $existingReview = Review::where('course_id', $course->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingReview) {
            throw ValidationException::withMessages([
                'content_id' => 'You have already reviewed this course.',
            ]);
        }

        $review = new Review([
            'star' => $validatedData['star'],
            'comment' => $validatedData['comment'],
        ]);

        $review->user()->associate(auth()->user());
        $review->course()->associate($course);
        $review->save();

        return response()->json(['message' => 'Review added successfully'], 201);
    }

    public function update(Request $request,$id)
    {
        $validatedData = $request->validate([
            'star' => ['required', 'numeric', 'between:0,5', 'step:0.5'],
            'comment' => ['nullable', 'string'],
        ]);

        $course = Course::findOrFail($id);
        $userId = auth()->user()->id;

        // Retrieve the review for the specified course and user
        $review = $course->reviews()->where('user_id', $userId)->first();

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->star = $validatedData['star'];
        $review->comment = $validatedData['comment'];
        $review->save();

        return response()->json(['message' => 'Review updated successfully']);
    }

    public function destroy($id)
    {

        $course = Course::findOrFail($id);
        $userId = auth()->user()->id;

        // Retrieve the review for the specified course and user
        $review = $course->reviews()->where('user_id', $userId)->first();

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }

    public function getReviews(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        if ($course == null) {
            return response()->json(['message' => 'Content is not a course'], 422);
        }

        $validatedData = $request->validate([
            'order' => ['nullable', Rule::in(['asc', 'desc'])], // Validate that the order parameter is either 'asc' or 'desc'
        ]);

        $reviewsQuery = Review::where('course_id', $course->id)
            ->where('user_id', '!=', auth()->user()->id)
            ->with('user:id,name')
            ->select('id', 'star', 'comment', 'user_id', 'created_at', 'updated_at');

        if ($request->has('order')) {
            $orderBy = $validatedData['order'];
            $reviewsQuery->orderBy('star', $orderBy); // Order the reviews by the "star" column
        }

        $userReview = Review::where('course_id', $course->id)
            ->where('user_id', auth()->user()->id)
            ->with('user:id,name')
            ->select('id', 'star', 'comment', 'user_id', 'created_at', 'updated_at')
            ->first();

        if ($reviewsQuery->get()->isEmpty()) {
            return response()->json(['user_review' => $userReview, 'reviews' => null]);
        }

        return response()->json(['user_review' => $userReview, 'reviews' => $reviewsQuery->paginate(5)]);
    }
}
