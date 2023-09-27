<?php

use App\Http\Controllers\AuthenticationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//register route
Route::post('/register', [AuthenticationController::class,'register']);
Route::post('/login',[AuthenticationController::class,'login']);
Route::post('/logout',[AuthenticationController::class,'logout'])->middleware('auth:sanctum');
Route::get('/getUser',[AuthenticationController::class,'getCurrentUser'])->middleware('auth:sanctum');
