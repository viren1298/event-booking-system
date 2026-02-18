<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// Public Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Event Routes
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);

// Protected Routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth Routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Event Routes (Organizer/Admin only for create/update/delete)
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);

    // Ticket Routes (Organizer/Admin only)
    Route::post('/events/{eventId}/tickets', [TicketController::class, 'store']);
    Route::put('/tickets/{id}', [TicketController::class, 'update']);
    Route::delete('/tickets/{id}', [TicketController::class, 'destroy']);

    // Booking Routes
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/tickets/{ticketId}/bookings', [BookingController::class, 'store'])->middleware('prevent.double.booking');
    Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

    // Payment Routes
    Route::post('/bookings/{bookingId}/payment', [PaymentController::class, 'store']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
});

