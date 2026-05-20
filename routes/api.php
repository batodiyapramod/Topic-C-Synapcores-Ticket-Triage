<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::post('/tickets', [TicketWebhookController::class, 'store']);
Route::post('/tickets', [DashboardController::class, 'handleWebhook']);
