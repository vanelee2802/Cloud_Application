<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    /**
     * Mitarbeiter-Dashboard
     */
  public function index(Request $request)
{
    
    $employees = User::where('role', 'employee')
        ->where('nail_studio_id', $request->user()->nail_studio_id)
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'email',
            'avatar',
            'role',
            'nail_studio_id',
        ]);


    return Inertia::render('EmployeeManagement', [
    'employees' => $employees,
    ]);
}

    /**
     * Mitarbeiter hinzuf++gen
     */
    public function store(Request $request)
    {
        $admin = $request->user();

        // Nur Admins d++rfen Mitarbeiter hinzuf++gen
        if ($admin->role !== 'admin') {
            abort(403, 'Nur ein Admin kann Mitarbeiter hinzuf++gen.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ]);


        /*
         * Pr++fen, ob bereits ein Account mit dieser
         * E-Mail-Adresse existiert.
         */
        $employee = User::where(
            'email',
            $validated['email']
        )->first();


        /*
         * ACCOUNT EXISTIERT NOCH NICHT
         */
        if (!$employee) {

            $employee = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],

                /*
                 * Der Mitarbeiter meldet sich sp+�ter
                 * ++ber Google an.
                 *
                 * Deshalb brauchen wir hier kein
                 * vom Mitarbeiter eingegebenes Passwort.
                 */
                'password' => Hash::make(
                    Str::random(40)
                ),

                'role' => 'employee',

                /*
                 * Mitarbeiter geh+�rt zum gleichen
                 * Studio wie der Admin.
                 */
                'nail_studio_id' => $admin->nail_studio_id,

                'email_verified_at' => now(),
            ]);

        
            } else {
                // Bestehende Admin- und Mitarbeiterkonten nicht verändern.
                if (in_array($employee->role, ['admin', 'employee'], true)) {
                    return redirect()
                        ->back()
                        ->withErrors([
                            'email' => 'Diese E-Mail-Adresse gehört bereits zu einem Admin oder Mitarbeiter.',
                        ]);
                }

                // Nur ein bestehendes Kundenkonto darf zum Mitarbeiter werden.
                $employee->update([
                    'name' => $validated['name'],
                    'role' => 'employee',
                    'nail_studio_id' => $admin->nail_studio_id,
                ]);
            }



        return redirect()
            ->back()
            ->with(
                'success',
                'Mitarbeiter wurde erfolgreich hinzugef++gt.'
            );
    }


    /**
     * Mitarbeiter entfernen
     */
    public function destroy(
        Request $request,
        User $employee
    ) {
        $admin = $request->user();

        // Nur Admins d++rfen Mitarbeiter entfernen
        if ($admin->role !== 'admin') {
            abort(403, 'Nur ein Admin kann Mitarbeiter entfernen.');
        }


        /*
         * Sicherheitspr++fung:
         *
         * Der Mitarbeiter muss zum gleichen
         * Studio geh+�ren.
         */
        if (
            $employee->nail_studio_id !==
            $admin->nail_studio_id
        ) {
            abort(403, 'Dieser Mitarbeiter geh+�rt nicht zu deinem Studio.');
        }


        /*
         * Der aktuell eingeloggte Admin darf sich
         * nicht selbst entfernen.
         */
        if ($employee->id === $admin->id) {
            abort(
                422,
                'Du kannst dich nicht selbst als Mitarbeiter entfernen.'
            );
        }


        /*
         * Wir l+�schen den Account nicht komplett.
         *
         * Stattdessen wird die Rolle wieder auf
         * customer gesetzt.
         *
         * So bleiben Designs, Termine usw. erhalten.
         */
        $employee->update([
            'role' => 'customer',
            'nail_studio_id' => null,
        ]);


        return redirect()
            ->back()
            ->with(
                'success',
                'Mitarbeiter wurde entfernt.'
            );
    }
}
