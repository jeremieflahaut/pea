<?php

use App\Http\Controllers\AllocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::middleware(['web'])->post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::middleware(['web'])->post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::middleware(['auth:sanctum'])->group(function () {

    Route::apiResource('positions', PositionController::class)->only('index');
    Route::apiResource('allocations', AllocationController::class);
    Route::apiResource('transactions', TransactionController::class)->except(['show', 'destroy']);


    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('user.show');
});
