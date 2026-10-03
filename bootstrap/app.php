<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Controllers\EmployeeController;

#für stripe
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
   ->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',

    then: function () {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/stripe.php'));
    },
)
    ->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
        \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
    ]);

    $middleware->alias([
        'role' => \App\Http\Middleware\EnsureUserHasRole::class,
    ]);
})

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

    #mitarbeiter hinzufügen 
    Route::middleware(['auth', 'role:employee,admin'])->group(function () {

    Route::get('/Employer', [
        EmployeeController::class,
        'index'
    ])->name('Employer');

});


Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::post('/employees', [
        EmployeeController::class,
        'store'
    ])->name('employees.store');

    Route::delete('/employees/{employee}', [
        EmployeeController::class,
        'destroy'
    ])->name('employees.destroy');

});
