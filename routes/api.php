<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/v1')->group(function() {
    Route::prefix('/auth')->controller(AuthController::class)->group(function() {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/logout', 'logout');
    });
    
    Route::middleware(AuthCheck::class)->group(function() {
        Route::prefix('/posts')->controller(PostController::class)->group(function() {
            Route::post('/create', 'create');
            Route::get('/index', 'index');
            Route::delete('/{id}', 'delete');
        });

        Route::prefix('/users')->controller(UserController::class)->group(function() {
            Route::get('/', 'getUsers');
            Route::get('/{username}', 'getUserDetail');
        });

        Route::prefix('/users/{username}')->controller(FollowController::class)->group(function() {
            Route::get('/followers', 'getfollowers');
            Route::put('/accept', 'acceptFollow');
            Route::post('/follow', 'following');
            Route::delete('/unfollow', 'unfollow');
        });

        Route::get('/following', [FollowController::class, 'getFollowing']);
    });
});