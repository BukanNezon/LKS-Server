<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function following($username) {
        $userLogin = Auth::guard('sanctum')->user();

        $user = User::where('username', $username)->with('followers')->first();
        if(!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        } 

        if($userLogin->username == $username) {
            return response()->json([
                'message' => 'You are not allowed to follow yourself'
            ], 422);
        }

        $follow = Follow::where('following_id', $user->id)->where('follower_id', $userLogin->id)->first();

        if($follow) {
            return response()->json([
                'message' => 'You are already followed',
                'status' => $follow->is_accepted == 1 ? 'following' : 'requested'
            ]);
        }

        $newFollow = Follow::create([
            'following_id' => $user->id,
            'follower_id' => $userLogin->id
        ]);

        return response()->json([
            'message' => 'Follow success',
            'status' => $newFollow->is_accepted == 1 ? 'following' : 'requested'
        ]);
    }


    public function unfollow($username) {
        $userLogin = Auth::guard('sanctum')->user();

        $user = User::where('username', $username)->with('followers')->first();
        if(!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        } 

        $follow = Follow::where('following_id', $user->id)->where('follower_id', $userLogin->id)->first();
        if(!$follow) {
            return response()->json([
                'message' => 'You are not following the user'
            ], 422);
        }

        $follow->delete();
        return response(null, 204);
    }

    public function acceptFollow($username) {
        $userLogin = Auth::guard('sanctum')->user();

        $user = User::where('username', $username)->first();
        if(!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        } 

        $follow = Follow::where('following_id', $userLogin->id)->where('follower_id', $user->id)->first();
        if(!$follow) {
            return response()->json([
                'message' => 'The user is not following you'
            ], 422);
        }

        if($follow->is_accepted) {
            return response()->json([
                'message' => 'Follow request is already accepted'
            ], 422);
        }

        $follow->is_accepted = 1;
        $follow->save();
        return response()->json([
            'message' => 'Follow Request Accepted'
        ], 200);
    }

    public function getFollowing() {
        $userLogin = Auth::guard('sanctum')->user();
        
        $user = User::where('username', $userLogin->username)->with('followings', 'followings.follower')->first();
        if(!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        } 

        return response()->json([
            'following' => $user->followings->map(function($following){
                return [
                    'id' => $following->follower->id,
                    'full_name' => $following->follower->full_name,
                    'username' => $following->follower->username,
                    'bio' => $following->follower->bio,
                    'is_private' => $following->follower->is_private,
                    'created_at' => $following->follower->created_at,
                    'is_requested' => !$following->is_accepted

                ];
            })
        ]);
    }

    public function getFollowers($username) {
        $user = User::where('username', $username)->with('followers', 'followers.following')->first();
        if(!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        } 

        return response()->json([
            'followers' => $user->followers->map(function($follower){
                return [
                    'id' => $follower->following->id,
                    'full_name' => $follower->following->full_name,
                    'username' => $follower->following->username,
                    'bio' => $follower->following->bio,
                    'is_private' => $follower->following->is_private,
                    'created_at' => $follower->following->created_at,
                    'is_requested' => !$follower->is_accepted

                ];
            })
        ]);
    }
}
