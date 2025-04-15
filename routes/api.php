<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::put('/user/update', [PersonController::class, 'update'])->middleware('auth');
Route::post('/user/update-image', [PersonController::class, 'updateImageProfile'])->middleware('auth');
Route::patch('/user/change-password', [PersonController::class, 'changePassword'])->middleware('auth');

Route::get('/user/posts', [PersonController::class, 'index'])->middleware('auth');
Route::get('/post/{id}/show', [PostController::class, 'show'])->middleware('auth');

// posts api
Route::get('/posts', [PostController::class, 'index'])->middleware('auth');
Route::post('/post/create', [PostController::class, 'create'])->middleware('auth');
Route::get('/post/{id}/edit', [PostController::class, 'edit'])->middleware('auth');
Route::post('/post/{id}/update', [PostController::class, 'update'])->middleware('auth');
Route::delete('/post/{id}/delete', [PostController::class, 'destroy'])->middleware('auth');

// comments api
Route::post('/comment/create', [CommentController::class, 'create'])->middleware('auth');
Route::get('/comment/{id}/edit', [CommentController::class, 'edit'])->middleware('auth');
Route::patch('/comment/{id}/update', [CommentController::class, 'update'])->middleware('auth');
Route::delete('/comment/{id}/delete', [CommentController::class, 'destroy'])->middleware('auth');

// tags api
Route::get('/tags', [TagController::class, 'index'])->middleware('auth');
