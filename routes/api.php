<?php

use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SaveController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// access to end point for any one authonticate
Route::middleware(['auth'])->group(function() {

    // users api
    Route::get('/users', [PersonController::class, 'index']);
    Route::put('/user/update', [PersonController::class, 'update']);
    Route::post('/user/update-image', [PersonController::class, 'updateImageProfile']);
    Route::patch('/user/change-password', [PersonController::class, 'changePassword']);
    Route::get('/search-user', [PersonController::class, 'searchUser']);

    // tags api
    Route::get('/tags', [TagController::class, 'index']);

    // posts api
    Route::get('/posts', [PostController::class, 'index']);
    Route::delete('/post/{id}/delete', [PostController::class, 'destroy']);
});

// end point for just admin role
Route::middleware(['auth', 'role:admin'])->group(function() {

    // users api
    Route::patch('user/{userId}/ban', [PersonController::class, 'toggleUserBan']);
    Route::delete('user/{userId}/delete', [PersonController::class, 'destroy']);
    Route::get('users/total', [PersonController::class, 'totalUsers']);

    // tags api
    Route::post('tag/store', [TagController::class, 'store']);
    Route::get('tag/{tagId}/edit', [TagController::class, 'edit']);
    Route::patch('tag/{tagId}/update', [TagController::class, 'update']);
    Route::delete('tag/{tagId}/delete', [TagController::class, 'destroy']);
    Route::get('tags/search', [TagController::class, 'searchTags']);

    // posts api
    Route::get('posts/search', [PostController::class, 'searchPosts']);
    Route::get('posts/total', [PostController::class, 'totalPosts']);
    Route::patch('post/{postId}/status', [PostController::class, 'toggleStatusPost']);

    // messages api
    Route::get('messages/total', [MessageController::class, 'totalMessages']);

    // comments api
    Route::get('comments/total', [CommentController::class, 'totalComments']);

});

// end point for just user role
Route::middleware(['auth', 'role:user'])->group(function() {

    // users api
    Route::get('/user/{userId}/follow-status', [PersonController::class, 'followStatus']);
    Route::post('/user/{userId}/toggle-follow', [PersonController::class, 'toggleFollow']);
    Route::get('/user/{id}/show', [PersonController::class, 'show']);
    Route::get('/user/users-not-follow-back', [PersonController::class, 'getAllNotFollowBack']);
    Route::post('/user/{id}/block', [PersonController::class, 'toggleUserBlock']);
    Route::get('/user/{id}/block-status', [PersonController::class, 'blockStatus']);
    Route::get('/users/blocked', [PersonController::class, 'listUsersBlocked']);
    Route::patch('logout', [AuthController::class, 'logout']);

    // messages api
    Route::get('/contacts', [MessageController::class, 'contacts']);
    Route::get('/contact/{friend_id}/conversation', [MessageController::class, 'getConversation']);
    Route::post('message/send', [MessageController::class, 'sendMessage']);
    Route::get('/message/{id}/edit', [MessageController::class, 'edit']);
    Route::patch('/message/{id}/update', [MessageController::class, 'update']);
    Route::delete('/message/{id}/delete', [MessageController::class, 'destroy']);
    Route::get('/messages/status', [MessageController::class, 'getStatusMessages']);
    Route::patch('messages/mark-as-read', [MessageController::class, 'changeStatusMessage']);

    // saves api
    Route::post('save/{postId}/post', [SaveController::class, 'toggleSavePost']);
    Route::get('my-saved/posts', [SaveController::class, 'mySavedPosts']);

    // posts api
    Route::post('/post/create', [PostController::class, 'create']);
    Route::get('/post/{id}/show', [PostController::class, 'show']);
    Route::get('/post/{id}/edit', [PostController::class, 'edit']);
    Route::post('/post/{id}/update', [PostController::class, 'update']);

    // comments api
    Route::post('/comment/create', [CommentController::class, 'store']);
    Route::get('/comment/{id}/edit', [CommentController::class, 'edit']);
    Route::patch('/comment/{id}/update', [CommentController::class, 'update']);
    Route::delete('/comment/{id}/delete', [CommentController::class, 'destroy']);

    // likes api
    Route::post('/like/add', [LikeController::class, 'create']);
    Route::post('/like/show', [LikeController::class, 'show']);
    Route::delete('/like/delete', [LikeController::class, 'destroy']);

    // notifications api
    Route::get('/user/notifications', [NotificationController::class, 'index']);
    Route::delete('notifications/clear', [NotificationController::class, 'destroy']);
    Route::patch('notifications/mark', [NotificationController::class, 'update']);
});

Route::get('/check-user-auth', [PersonController::class, 'checkUserAuth'])->middleware(['auth']);
