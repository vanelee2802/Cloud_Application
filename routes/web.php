<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\NailStudioController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\NailShapeController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\DesignElementController;
use App\Http\Controllers\DesignController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CartItemController;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/studio', [NailStudioController::class, 'show'])->name('studio.show');
    Route::patch('/studio', [NailStudioController::class, 'update'])->name('studio.update');

    Route::apiResource('services', ServiceController::class);
    Route::apiResource('nail-shapes', NailShapeController::class);
    Route::apiResource('colors', ColorController::class);
    Route::apiResource('design-elements', DesignElementController::class);
    Route::apiResource('designs', DesignController::class);
    Route::apiResource('appointments', AppointmentController::class);
    Route::apiResource('payments', PaymentController::class);
Route::apiResource('notifications', NotificationController::class)->only(['index', 'update', 'destroy']);
Route::apiResource('cart-items', CartItemController::class)->only(['index', 'store', 'destroy']);
});

require __DIR__.'/auth.php';
