<?php

use App\Http\Controllers\AuthController;
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

Route::get('/posts', [PostController::class, 'index'])->middleware('auth');
Route::post('/post/create', [PostController::class, 'create'])->middleware('auth');
Route::get('/post/{id}/edit', [PostController::class, 'edit'])->middleware('auth');
Route::post('/post/{id}/update', [PostController::class, 'update'])->middleware('auth');

Route::get('/tags', [TagController::class, 'index'])->middleware('auth');
