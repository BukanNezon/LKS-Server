<?php

namespace App\Http\Controllers;

use App\Models\PostAttachment;
use App\Models\Posts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function create(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'caption' => 'required',
            'attachments' => 'required|array',
            'attachments.*' => 'mimes:png,jpg,jpeg,webp,gif'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Invalid Field',
                'errors' => $validator->errors()
            ]);
        }

        $user = Auth::guard('sanctum')->user();


        $newPost = Posts::create([
            'caption' => $request->caption,
            'user_id' => $user->id
        ]);

        foreach($request->attachments as $attachments) {
            $file_name = time() . '_' . $attachments->getClientOriginalName();
            $attachments->storeAs('attachments', $file_name, 'public');

            $newPost->attachments()->create([
                'storage_path' => 'attachments/' . $file_name,
            ]);
        }

        return response()->json([
            'message' => 'Create post success'
        ], 201);
    }

    public function delete($id) 
    {
        $post = Posts::find($id);

        if(!$post) {
            return response()->json([
                'message' => 'Post Not Found'
            ], 404);
        }

        $user = Auth::guard('sanctum')->user();

        if($user->id != $post->user_id) {
            return response()->json([
                'message' => 'Forbidden Access'
            ], 403);
        }

        $post->delete();

        return response(null, 204);
    }

    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:0',
            'size' => 'integer|min:1'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $page = $request->input('page', 0);
        $size = $request->input('size', 10);

        $posts = Posts::with(['user', 'attachments'])
                    ->orderBy('created_at', 'desc')
                    ->skip($page * $size)
                    ->take($size)
                    ->get();

        $response = [
            'page' => $page,
            'size' => $size,
            'posts' => $posts->map(function ($post) {
                return [
                    'id' => $post->id,
                    'caption' => $post->caption,
                    'created_at' => $post->created_at,
                    'deleted_at' => $post->deleted_at,
                    'user' => [
                        'id' => $post->user->id,
                        'full_name' => $post->user->name,
                        'username' => $post->user->username,
                        'bio' => $post->user->bio,
                        'is_private' => $post->user->is_private,
                        'created_at' => $post->user->created_at,
                    ],
                    'attachments' => $post->attachments->map(function ($attachment) {
                        return [
                            'id' => $attachment->id,
                            'storage_path' => $attachment->storage_path,
                        ];
                    })
                ];
            }),
        ];

        return response()->json($response, 200);
    }

}
