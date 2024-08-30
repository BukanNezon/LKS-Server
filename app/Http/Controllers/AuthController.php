<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'bio' => 'required|max:100',
            'username' => 'required|min:3|unique:users,username|regex:/^[a-zA-Z0-9._]+$/',
            'password' => 'required|min:6',
            'is_private' => 'boolean'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Invalid Field',
                'errors' => $validator->errors()
            ], 422);
        }

        $newUser = User::create([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'password' => $request->password,
            'bio' => $request->bio,
            'is_private' => $request->is_private ?? false,
        ]);

        $token = $newUser->createToken("Auth Token")->plainTextToken;

        return response()->json([
            'message' => 'Register Success',
            'token' => $token,
            'data' => [
                'full_name' => $newUser->full_name,
                'bio' => $newUser->bio,
                'username' => $newUser->username,
                'is_private' => $newUser->is_private,
                'id' => $newUser->id
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if(auth()->attempt($credentials)) {
            $token = auth()->user()->createToken('Auth Token')->plainTextToken;
            $user = auth()->user();

            return response()->json([
                'message' => 'Login Success',
                'token' => $token,
                'user' => $user
            ], 200);
        } else {
            return response()->json([
                'message' => 'Wrong Username Or Password'
            ], 401);
        }
    }

    public function logout(Request $request) 
    {
        if(Auth::guard('sanctum')->check()) {
            Auth::guard('sanctum')->user()->tokens()->delete();
            return response()->json([
                'message' => 'Logout Success'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }
    }
}
