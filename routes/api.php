<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Middleware\Authenticate;
use App\Http\Controllers\AddFriendController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// public route
Route::post('register',[AuthController::class,'register']);
Route::get('emailVarification/{token}/{email}',[AuthController::class,'emailVarification']);
Route::post('login',[AuthController::class,'login']);
Route::post('logout',[AuthController::class,'logout']);

// profile & Posts
Route::middleware(['UserMiddleware'])->group(function () {
    // Route::get('profile',[AuthController::class,'profile']);
    // Route::post('create',[PostController::class,'create']);
    // Route::get('show',[PostController::class,'show']);
    // Route::post('update/{id}',[PostController::class,'update']);
    // Route::post('delete/{id}',[PostController::class,'delete']);
});
Route::get('profile',[AuthController::class,'profile']);
Route::post('create',[PostController::class,'create']);
Route::get('show',[PostController::class,'show']);
Route::post('update/{id}',[PostController::class,'update']);
Route::post('delete/{id}',[PostController::class,'delete']);

//Add Friend
Route::post('addFriend',[AddFriendController::class,'addFriend']);
Route::post('acceptFriendRequest/{id}',[AddFriendController::class,'acceptFriendRequest']);