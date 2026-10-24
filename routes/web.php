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
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\DesignEditorController;
use App\Models\NailStudio;
use App\Models\User;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\StudioPriceController;

Route::apiResource('colors', ColorController::class)->except(['index', 'show']);
Route::get('/colors', [ColorController::class, 'index']);

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
Route::get('/payment/cancel', function () {
    return 'Zahlung abgebrochen.';
});
Route::get('/payment/success', function () {
    return 'Zahlung erfolgreich!';
});

// Rückkehr von Stripe – auch für Gäste erreichbar
Route::get('/buchung/erfolg', function () {
    return Inertia::render('BookingResult', ['success' => true]);
})->name('booking.success');

Route::get('/buchung/abgebrochen', function () {
    return Inertia::render('BookingResult', ['success' => false]);
})->name('booking.cancelled');
#Route::get('/design-editor', [DesignEditorController::class, 'index']);
#})->name('welcome');

Route::get('/dashboard', function () {
    $role = auth()->user()->role;

    if ($role === 'owner') {
        return redirect('/Studioverwaltung');
    }

    if (in_array($role, ['admin', 'employee'])) {
        return redirect('/StudioDashboard');
    }

    return redirect('/DesignEditor');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/checkout', function () {
    return Inertia::render('Checkout');
})->name('checkout');

// Termin buchen – auch ohne Kundenkonto möglich
Route::get('/appointments/availability', [AppointmentController::class, 'availability']);
Route::post('/appointments/hold', [AppointmentController::class, 'hold'])->middleware('throttle:20,1');
Route::delete('/appointments/{appointment}/hold', [AppointmentController::class, 'releaseHold']);
Route::post('/appointments/{appointment}/submit', [AppointmentController::class, 'submit'])->middleware('throttle:10,1');

// NEU: Kundin wählt einen Ersatztermin (eingeloggt oder per signiertem Link)
Route::post('/appointments/{appointment}/alternatives/{alternative}/accept', [AppointmentController::class, 'acceptAlternative'])
    ->middleware('throttle:10,1')
    ->name('appointments.alternatives.accept');
// Bestätigten Termin bezahlen (eingeloggt oder per signiertem Link)
Route::post('/appointments/{appointment}/pay', [AppointmentController::class, 'pay'])
    ->middleware('throttle:10,1')
    ->name('appointments.pay');
    // Terminseite aus der E-Mail (eingeloggt oder per signiertem Link)
Route::get('/termin/{appointment}', [AppointmentController::class, 'manage'])
    ->name('appointments.manage');

// Muster-Katalog: für alle lesbar (auch ohne Login), damit der Design-Editor funktioniert.
// Anlegen/Ändern/Löschen bleibt unten im geschützten Bereich.
Route::get('/design-elements', [DesignElementController::class, 'index']);
Route::middleware('auth')->group(function () {

    // Übersicht der gespeicherten Nageldesigns
    Route::get('/meine-designs', function () {
        return Inertia::render('Designs/Index');
    })->name('designs.page');
    // Benutzerverwaltung – nur für die Inhaberin
    Route::get('/admin/users', [UserManagementController::class, 'index'])
        ->middleware('role:owner')
        ->name('admin.users.index');

    Route::patch('/admin/users/{user}', [UserManagementController::class, 'update'])
        ->middleware('role:owner')
        ->name('admin.users.update');
        // Rechnungen: Kundin sieht eigene, Download mit Rechteprüfung
    Route::get('/rechnungen', [ReceiptController::class, 'index'])
        ->name('receipts.index');
    Route::get('/rechnungen/{receipt}/pdf', [ReceiptController::class, 'download'])
        ->name('receipts.download');

    // Rechnungen des Studios: Inhaberin und Schichtleitung
    Route::get('/studio/rechnungen', [ReceiptController::class, 'studioIndex'])
        ->middleware('role:owner,admin')
        ->name('receipts.studio');    

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

   #studioDashboard für Admin und Employee
   # Route::get('/studio-dashboard', function () {
   # return Inertia::render('StudioDashboard');
#})->middleware('role:employee,admin')->name('studio.dashboard');

    Route::get('/studio', [NailStudioController::class, 'show'])->name('studio.show');
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);
    Route::get('/nail-shapes', [NailShapeController::class, 'index']);

    Route::apiResource('designs', DesignController::class);
    Route::apiResource('appointments', AppointmentController::class)->only(['index', 'show']);
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'update', 'destroy']);
    Route::apiResource('cart-items', CartItemController::class)->only(['index', 'store', 'destroy']);
    Route::post('/payments/{id}/checkout', [PaymentController::class, 'checkout']);
            Route::apiResource('design-elements', DesignElementController::class)->except(['index', 'show']);


    Route::middleware('role:employee,admin')->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
        Route::patch('/studio', [NailStudioController::class, 'update'])->name('studio.update');

        Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
        Route::apiResource('nail-shapes', NailShapeController::class)->except(['index', 'show']);

        Route::patch('/appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);
        Route::apiResource('payments', PaymentController::class);
    });

        // Studioleitung: Anfragen für heute bearbeiten
    Route::middleware('role:admin')->prefix('admin/appointments')->group(function () {
        Route::get('/requests', [\App\Http\Controllers\Admin\AppointmentRequestController::class, 'index']);
        Route::get('/bookings', [\App\Http\Controllers\Admin\AppointmentRequestController::class, 'bookings']);
        Route::post('/{appointment}/confirm', [\App\Http\Controllers\Admin\AppointmentRequestController::class, 'confirm']);
        Route::post('/{appointment}/decline', [\App\Http\Controllers\Admin\AppointmentRequestController::class, 'decline']);
        Route::post('/{appointment}/alternatives', [\App\Http\Controllers\Admin\AppointmentRequestController::class, 'offerAlternatives']);
    });

    // Studioverwaltung + Mitarbeiter verwalten: Inhaberin und Schichtleitung
    Route::middleware('role:owner,admin')->group(function () {

        Route::get('/Studioverwaltung', function () {

            $user      = auth()->user();
            $studioIds = $user->manageableStudioIds();

            // Mitarbeiterinnen aller Studios, die diese Person verwalten darf
            $employees = User::query()
                ->where('role', User::ROLE_EMPLOYEE)
                ->whereIn('nail_studio_id', $studioIds)
                ->with('nailStudio:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'nail_studio_id']);

            return Inertia::render('Studioverwaltung', [
                'employees' => $employees,
                // Nur die Inhaberin bekommt eine Studio-Auswahl
                'studios'   => $user->isOwner()
                    ? $user->ownedStudios()->orderBy('name')->get(['id', 'name'])
                    : [],
            ]);

        })->name('Studioverwaltung');

        Route::post('/employees', [EmployeeController::class, 'store'])
            ->name('employees.store');

        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])
            ->name('employees.destroy');
    });

        // Preise pro Studio – nur für die Inhaberin
    Route::get('/admin/preise', [StudioPriceController::class, 'index'])
        ->middleware('role:owner')
        ->name('admin.prices.index');

    Route::put('/admin/preise/{studio}', [StudioPriceController::class, 'update'])
        ->middleware('role:owner')
        ->name('admin.prices.update');
});

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);


Route::get('/DesignEditor', [DesignEditorController::class, 'index'])
    ->name('DesignEditor');
#StudioDashboard zugang ohne Admin rechte
Route::get('/StudioDashboard', function () {
    return Inertia::render('StudioDashboard');
})->name('StudioDashboard');

Route::get('/Leistungen', function () {
    return Inertia::render('Leistungen');
})->name('Leistungen');


Route::get('/impressum', function () {
    return Inertia::render('AppFooter/impressum');
})->name('impressum');

Route::get('/datenschutz', function () {
    return Inertia::render('AppFooter/datenschutz');
})->name('datenschutz');

Route::get('/AGB', function () {
    return Inertia::render('AppFooter/AGB');
})->name('AGB');

Route::get('/kontakt', function () {
    return Inertia::render('AppFooter/kontakt');
})->name('kontakt');

Route::get('/Rezensionen', function () {
    return Inertia::render('Rezension');
})->name('Rezensionen');

Route::get('/StudioSearch', function () {
    return Inertia::render('StudioSearch');
})->name('StudioSearch');





require __DIR__.'/auth.php';