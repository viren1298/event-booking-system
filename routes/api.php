<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);

Route::middleware('auth:sanctum')->group(function(){

    Route::post('/logout',[AuthController::class,'logout']);
    Route::get('/me',[AuthController::class,'me']);

    Route::get('/events',[EventController::class,'index']);

    Route::middleware('role:organizer')->group(function(){
        Route::post('/events',[EventController::class,'store']);
    });

    Route::middleware(['role:customer','prevent.double.booking'])
        ->post('/tickets/{id}/bookings',[BookingController::class,'store']);
});
