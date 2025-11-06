<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BlogController;
Route::get('/test', function() {
    return response()->json(['message' => 'API working']);
});
// Public routes
Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function(){
    Route::get('blogs',[BlogController::class,'index']);
    Route::post('blogs',[BlogController::class,'store']);
    Route::post('blogs_update/{blog}',[BlogController::class,'update']);
    Route::delete('blogs/{blog}',[BlogController::class,'destroy']);
    Route::post('blogs/{blog}/toggle-like',[BlogController::class,'toggleLike']);
});
