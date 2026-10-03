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
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'email',
            'avatar',
            'role',
            'nail_studio_id',
        ]);

    dd($employees);
}

    /**
     * Mitarbeiter hinzufügen
     */
    public function store(Request $request)
    {
        $admin = $request->user();

        // Nur Admins dürfen Mitarbeiter hinzufügen
        if ($admin->role !== 'admin') {
            abort(403, 'Nur ein Admin kann Mitarbeiter hinzufügen.');
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
         * Prüfen, ob bereits ein Account mit dieser
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
                 * Der Mitarbeiter meldet sich später
                 * über Google an.
                 *
                 * Deshalb brauchen wir hier kein
                 * vom Mitarbeiter eingegebenes Passwort.
                 */
                'password' => Hash::make(
                    Str::random(40)
                ),

                'role' => 'employee',

                /*
                 * Mitarbeiter gehört zum gleichen
                 * Studio wie der Admin.
                 */
                'nail_studio_id' => $admin->nail_studio_id,

                'email_verified_at' => now(),
            ]);

        } else {

            /*
             * ACCOUNT EXISTIERT BEREITS
             *
             * Name aktualisieren
             * Rolle auf employee setzen
             * Studio zuweisen
             */
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
                'Mitarbeiter wurde erfolgreich hinzugefügt.'
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

        // Nur Admins dürfen Mitarbeiter entfernen
        if ($admin->role !== 'admin') {
            abort(403, 'Nur ein Admin kann Mitarbeiter entfernen.');
        }


        /*
         * Sicherheitsprüfung:
         *
         * Der Mitarbeiter muss zum gleichen
         * Studio gehören.
         */
        if (
            $employee->nail_studio_id !==
            $admin->nail_studio_id
        ) {
            abort(403, 'Dieser Mitarbeiter gehört nicht zu deinem Studio.');
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
         * Wir löschen den Account nicht komplett.
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