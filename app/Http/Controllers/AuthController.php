<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\RoleExists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Login a user and return a JWT token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Return error response if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Attempt to log in user with provided credentials
        try {
            if (!$token = JWTAuth::attempt($validator->validated())) {
                return response()->json(['error' => 'Invalid Credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // Get the authenticated user
        $user = auth()->user();

        // Return success response with JWT token and role_id
        return response()->json([
            'token' => $token,
            'role_id' => $user->role_id, // Assuming 'role_id' is a column in the users table
        ]);
    }


    /**
     * Register a new user and return a JWT token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id'
        ]);

        // Return error response if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Create new user with validated data
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role_id'=> $request->input('role_id')
        ]);


        // Generate JWT token for new user
        $token = JWTAuth::fromUser($user);

        // Return success response with token
        return response()->json(compact('token'));
    }

    /**
     * Refresh a user's JWT token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        // Get user from authenticated token
        $user = Auth::user();

        // Generate new JWT token for user
        $token = JWTAuth::fromUser($user);

        // Return success response with new token
        return response()->json(compact('token'));
    }

    /**
     * Log the user out and invalidate their token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // Invalidate user's token
        Auth::logout();

        // Return success response
        return response()->json(['message' => 'Successfully logged out']);
    }
}
