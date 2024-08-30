<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUserDetail($username)
    {   
        $user = User::where('username', $username)->with('posts', 'posts.attachments')->first();

        $userLogin = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $follow = Follow::where('follower_id', $userLogin->id)->where('following_id', $user->id)->first();

        $data = 
        [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'username' => $user->username,
            'bio' => $user->bio,
            'is_private' => $user->is_private,
            'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            'is_your_account' => $userLogin->id == $user->id,
            'following_status' => !$follow ? 'not-following': (!$follow->is_accepted ? 'requested' : 'following'),
            'posts_count' => $user->posts->count(),
            'followers_count' => $user->followers->count()??0,
            'following_count' => $user->followings->count()??0,
            
        ];

        if($user->is_private) {
            if($data['following_status'] == 'following') {
                $data['posts'] = $user->posts;
            }
        } else {
            $data['posts'] = $user->posts;
        }

        return response()->json($data, 200);
        
    }

    public function getUsers() {
        $userLogin = Auth::guard('sanctum')->user();

        $users = User::whereDoesntHave('followers', function ($query) use($userLogin) {
            $query->where('follower_id', $userLogin->id);
        })->where('id', '!=', $userLogin->id)->get();

        return response()->json([
            'users' => $users
        ], 200);
    }

    public function registerUser() {
        $user = Auth::guard('sanctum')->user();

        return response()->json([
            'message' => 'Register Success'
        ], 201);
    }
}
