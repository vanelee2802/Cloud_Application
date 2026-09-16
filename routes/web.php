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
use App\Http\Controllers\Auth\GoogleAuthController;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/Appointments', function () {
    return Inertia::render('Appointments');
})->name('Appointments');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/studio', [NailStudioController::class, 'show'])->name('studio.show');
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);
    Route::get('/nail-shapes', [NailShapeController::class, 'index']);
    Route::get('/colors', [ColorController::class, 'index']);
    Route::get('/design-elements', [DesignElementController::class, 'index']);

    Route::apiResource('designs', DesignController::class);
    Route::apiResource('appointments', AppointmentController::class)->only(['index', 'store', 'show']);
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'update', 'destroy']);
    Route::apiResource('cart-items', CartItemController::class)->only(['index', 'store', 'destroy']);

    Route::middleware('role:employee,admin')->group(function () {
        Route::patch('/studio', [NailStudioController::class, 'update'])->name('studio.update');

        Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
        Route::apiResource('nail-shapes', NailShapeController::class)->except(['index', 'show']);
        Route::apiResource('colors', ColorController::class)->except(['index', 'show']);
        Route::apiResource('design-elements', DesignElementController::class)->except(['index', 'show']);

        Route::patch('/appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);

        Route::apiResource('payments', PaymentController::class);
    });
});

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

Route::get('/DesignEditor', function () {
    return Inertia::render('DesignEditor');
})->name('DesignEditor');

require __DIR__.'/auth.php';