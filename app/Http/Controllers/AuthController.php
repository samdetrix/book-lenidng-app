<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Auth;
use App\Models\Role;
use Illuminate\Database\QueryException;


class AuthController extends Controller
{

    public function registerUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'phone' => 'nullable|string',
                'role_id' => 'required|exists:roles,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'data' => $validator->errors(),
                ], 422);
            }

            $existingUser = User::where('email', $request->email)->first();

            if ($existingUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User already exists',
                    'data' => null,
                ], 409);
            }

            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt('password'),
                'status' => 'active',
            ]);

            $user->save();

            $user->roles()->attach($request->role_id);

            // Lazy load roles
            $userWithRoles = User::with('roles')->find($user->id);


            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $userWithRoles
                ],
            ], 201);

        } catch (QueryException $exception) {
            if ($exception->errorInfo[1] == 1062) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Duplicate entry. User with this email or phone number already exists.',
                    'data' => null,
                ], 409);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'User registration failed',
                'data' => null,
            ], 422);
        }
    }


    public function loginUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'data' => $validator->errors(),
            ]);
        }

        if ($token = JWTAuth::attempt($validator->validated())) {
            $user = auth()->user();
            // Lazy load roles
            $userWithRoles = User::with('roles')->find($user->id);

            return response()->json([
                'status' => 'success',
                'message' => 'User signed in successfully',
                'data' => [
                    'user' => $userWithRoles,
                    'token' => $token,
                ],
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials',
            'data' => null,
        ]);
    }


    public function getUsers($user_id = null)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'data' => null,
            ], 401);
        }

        if (is_null($user_id)) {
            $users = User::latest()->get();

            if ($users->isNotEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Users Fetched Successfully',
                    'data' => $users,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No users found',
                    'data' => null,
                ]);
            }
        } else {
            $user = User::find($user_id);

            if ($user) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'User Fetched Successfully',
                    'data' => $user,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
        }
    }
    public function deleteUser($user_id)
    {
        $authenticatedUser = auth()->user();

        if (!$authenticatedUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'data' => null,
            ], 401);
        }

        $userToDelete = User::find($user_id);

        if ($userToDelete) {
            // Check if the authenticated user is trying to delete themselves
            if ($authenticatedUser->id === $userToDelete->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete the currently authenticated user',
                    'data' => null,
                ]);
            }

            $userToDelete->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully',
                'data' => null,
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User does not exist',
                'data' => null,
            ]);
        }
    }
    public function logout()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'data' => null,
            ], 401);
        }

        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Exception $e) {
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User logged out successfully',
            'data' => null,
        ]);
    }
}
