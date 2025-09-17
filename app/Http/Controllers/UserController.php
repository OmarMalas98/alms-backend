<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Update the authenticated user's account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user = auth()->user()->load('role');

        $validatedData = $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email',
        ]);

        $user->update($validatedData);

        $user->save();

        return response()->json([
            'message' => 'Account updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Get the authenticated user's profile details.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $user = auth()->user()->load('role');

        return response()->json([
            'user' => $user
        ]);
    }
        public function updatePassword(Request $request)
        {
            // Get authenticated user
            $user = Auth::user();

            // Validate input data
            $validatedData = $request->validate([
                'old_password' => 'required',
                'password' => 'required|confirmed|max:255|min:8',
            ]);

            // Check if old password matches
            if (!Hash::check($validatedData['old_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The old password is incorrect.'
                ], 422);
            }

            // Check if new password is different from old password
            if (Hash::check($validatedData['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The new password must be different from the old password.'
                ], 422);
            }

            // Update user password
            $user->password = Hash::make($validatedData['password']);
            $user->save();

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully.'
            ]);
        }
        public function dashboard()
        {
            $user=auth()->user();

            $courseCount=$user->coursesCount();
            $userCount=$user->usersCount();

            return [
                'courses_count' => $courseCount,
                'student_count' => $userCount,
            ];

        }
        public function enrolledUsers($id=null)
        {
            $user = auth()->user();
            if(!$id)
            {
             $enrolledUsers=$user->enrolledUsers();

            }
            else
            {
                $course =Course::findOrFail($id);
                $enrolledUsers=$course->enrolledUsers;
            }
            return response()->json(['enrolled_users' => $enrolledUsers]);
        }
    }


