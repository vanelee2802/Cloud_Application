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
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ShiftController;
use App\Models\EmployeeShift;

use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\ServiceCatalogController;

use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PublicReviewController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;

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

// Kundin wählt einen Ersatztermin (eingeloggt oder per signiertem Link)
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

// Öffentlich: Leistungen für die Leistungsseite
Route::get('/services/catalog', [ServiceCatalogController::class, 'index'])
    ->name('services.catalog');

// Bewertungsformular aus der Danke-Mail – nur mit gültigem signiertem Link
Route::get('/bewertung/{appointment}', [ReviewController::class, 'create'])
    ->middleware('signed')
    ->name('reviews.create');

Route::post('/bewertung/{appointment}', [ReviewController::class, 'store'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('reviews.store');

// Öffentlich: Bewertungen für die Startseite
Route::get('/reviews/public', [PublicReviewController::class, 'index'])
    ->name('reviews.public');

// Inhaberin: Bewertungen verwalten
Route::middleware(['auth', 'role:owner'])
    ->prefix('admin/bewertungen')
    ->name('admin.reviews.')
    ->group(function () {
        Route::get('/', [AdminReviewController::class, 'page'])->name('page');
        Route::get('/liste', [AdminReviewController::class, 'index'])->name('index');
        Route::patch('/{review}', [AdminReviewController::class, 'update'])->name('update');
    });


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

    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])
        ->middleware('role:owner')
        ->name('admin.users.destroy');

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

    // GEÄNDERT: Studio-Dashboard nur mit Login und passender Rolle (vorher für alle offen)
    Route::get('/StudioDashboard', function () {
        return Inertia::render('StudioDashboard');
    })->middleware('role:owner,admin,employee')->name('StudioDashboard');

    // GEÄNDERT: Einstellungen nur mit Login (vorher für alle offen)
    Route::get('/settings', function () {
        return Inertia::render('Setting');
    })->name('settings');

    Route::get('/studio', [NailStudioController::class, 'show'])->name('studio.show');
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);
    Route::get('/nail-shapes', [NailShapeController::class, 'index']);

    Route::apiResource('designs', DesignController::class);
    Route::apiResource('appointments', AppointmentController::class)->only(['index', 'show']);
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'update', 'destroy']);
    Route::apiResource('cart-items', CartItemController::class)->only(['index', 'store', 'destroy']);
    Route::post('/payments/{id}/checkout', [PaymentController::class, 'checkout']);

    Route::middleware('role:owner,admin,employee')->group(function () {
        Route::apiResource('colors', ColorController::class)->except(['index', 'show']);
        Route::apiResource('design-elements', DesignElementController::class)->except(['index', 'show']);
    });

    // GEÄNDERT: owner ergänzt – vorher bekam die Inhaberin hier 403 (leeres Dashboard)
    Route::middleware('role:owner,admin,employee')->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
        Route::patch('/studio', [NailStudioController::class, 'update'])->name('studio.update');

        Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
        Route::apiResource('nail-shapes', NailShapeController::class)->except(['index', 'show']);

        Route::patch('/appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);
        Route::apiResource('payments', PaymentController::class);
    });

    // Studioleitung: Anfragen für heute bearbeiten
    Route::middleware('role:owner,admin')->prefix('admin/appointments')->group(function () {
        Route::get('/requests', [\App\Http\Controllers\Admin\AppointmentRequestController::class, 'index']);
        Route::post('/{appointment}/confirm', [\App\Http\Controllers\Admin\AppointmentRequestController::class, 'confirm']);
        Route::post('/{appointment}/decline', [\App\Http\Controllers\Admin\AppointmentRequestController::class, 'decline']);
        Route::post('/{appointment}/alternatives', [\App\Http\Controllers\Admin\AppointmentRequestController::class, 'offerAlternatives']);
        Route::post('/{appointment}/complete', [\App\Http\Controllers\Admin\AppointmentRequestController::class, 'complete']);
    });

    // Gebuchte Termine: Schichtleitung und Mitarbeiterinnen dürfen sie sehen
    Route::get('/admin/appointments/bookings', [\App\Http\Controllers\Admin\AppointmentRequestController::class, 'bookings'])
        ->middleware('role:owner,admin,employee');

    // Studioverwaltung + Mitarbeiter verwalten: Inhaberin und Schichtleitung
    Route::middleware('role:owner,admin')->group(function () {

        Route::get('/Studioverwaltung', function () {

            $user      = auth()->user();
            $studioIds = $user->manageableStudioIds();

            // Mitarbeiterinnen aller Studios, die diese Person verwalten darf
            $employees = User::query()
                ->where('role', User::ROLE_EMPLOYEE)
                ->whereIn('nail_studio_id', $studioIds)
                ->with(['nailStudio:id,name', 'shifts'])
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'nail_studio_id'])
                ->map(fn (User $employee) => [
                    'id'          => $employee->id,
                    'name'        => $employee->name,
                    'email'       => $employee->email,
                    'nail_studio' => $employee->nailStudio,
                    'shifts'      => EmployeeShift::mapFor($employee),
                ]);

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

        // GEÄNDERT: Schichten ändern nur für Inhaberin und Schichtleitung (vorher ohne Rollenprüfung)
        Route::put('/employees/{employee}/shifts', [ShiftController::class, 'update'])
            ->name('shifts.update');
    });

    // Preise pro Studio – nur für die Inhaberin
    Route::get('/admin/preise', [StudioPriceController::class, 'index'])
        ->middleware('role:owner')
        ->name('admin.prices.index');

    Route::put('/admin/preise/{studio}', [StudioPriceController::class, 'update'])
        ->middleware('role:owner')
        ->name('admin.prices.update');

    // GEÄNDERT: Eigener Katalog pro Studio – jetzt innerhalb der Login-Gruppe
    Route::post('/admin/katalog/{studio}', [CatalogController::class, 'store'])
        ->middleware('role:owner')
        ->name('catalog.store');

    Route::delete('/admin/katalog/{studio}/{type}/{id}', [CatalogController::class, 'destroy'])
        ->middleware('role:owner')
        ->name('catalog.destroy');
});

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

Route::get('/DesignEditor', [DesignEditorController::class, 'index'])
    ->name('DesignEditor');

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

// Preisverwaltung: nur Inhaberin
Route::middleware(['auth', 'role:owner'])
    ->prefix('admin/services')
    ->name('admin.services.')
    ->group(function () {
        Route::get('/', [AdminServiceController::class, 'index'])->name('index');
        Route::post('/', [AdminServiceController::class, 'store'])->name('store');
        Route::patch('/{service}', [AdminServiceController::class, 'update'])->name('update');
        Route::delete('/{service}', [AdminServiceController::class, 'destroy'])->name('destroy');
    });

require __DIR__.'/auth.php';