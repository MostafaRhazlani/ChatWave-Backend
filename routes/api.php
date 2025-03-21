<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::put('/user/update', [PersonController::class, 'update'])->middleware('auth');

Route::get('/user/posts', [PersonController::class, 'index'])->middleware('auth');
Route::get('/post/{id}', [PostController::class, 'show'])->middleware('auth');
