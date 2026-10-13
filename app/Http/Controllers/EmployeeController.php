<?php

namespace App\Http\Controllers;

use App\Models\NailStudio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * Mitarbeiter zum Studio hinzufügen.
     *
     * Gibt es die E-Mail schon (z. B. Kundin mit Google-Login),
     * wird das bestehende Konto zur Mitarbeiterin gemacht.
     * Sonst wird ein neues Konto angelegt. Anmelden kann sie sich
     * dann mit "Mit Google anmelden" und genau dieser E-Mail.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'name'  => 'required|string|max:255',
                'email' => 'required|email|max:255',
            ],
            [
                'name.required'  => 'Bitte gib einen Namen ein.',
                'email.required' => 'Bitte gib eine E-Mail-Adresse ein.',
                'email.email'    => 'Bitte gib eine gültige E-Mail-Adresse ein.',
            ]
        );

        $email = Str::lower(trim($validated['email']));

        // Studio: das des Admins, sonst das erste vorhandene
        $studioId = $request->user()->nail_studio_id
            ?? NailStudio::query()->value('id');

        if (! $studioId) {
            return back()->withErrors([
                'email' => 'Es gibt noch kein Studio, dem der Mitarbeiter zugeordnet werden kann.',
            ]);
        }

        $user = User::where('email', $email)->first();

        if ($user?->role === 'admin') {
            return back()->withErrors([
                'email' => 'Diese E-Mail gehört zu einem Admin-Konto.',
            ]);
        }

        if ($user?->role === 'employee' && $user->nail_studio_id == $studioId) {
            return back()->withErrors([
                'email' => 'Diese Person ist bereits Mitarbeiter in diesem Studio.',
            ]);
        }

        if ($user) {

            // Bestehendes Konto zur Mitarbeiterin machen
            $user->update([
                'role'           => 'employee',
                'nail_studio_id' => $studioId,
            ]);

        } else {

            // Neues Konto (Passwort zufällig, Login läuft über Google)
            User::create([
                'name'           => trim($validated['name']),
                'email'          => $email,
                'password'       => Str::random(40),
                'role'           => 'employee',
                'nail_studio_id' => $studioId,
            ]);

        }

        return back()->with('success', 'Mitarbeiter wurde hinzugefügt.');
    }


    /**
     * Mitarbeiter aus dem Studio entfernen.
     *
     * Das Konto wird NICHT gelöscht, sondern wieder zur Kundin,
     * damit alte Termine mit dieser Mitarbeiterin erhalten bleiben.
     */
    public function destroy(User $employee): RedirectResponse
    {
        abort_unless($employee->role === 'employee', 404);

        $employee->update([
            'role'           => 'customer',
            'nail_studio_id' => null,
        ]);

        return back()->with('success', 'Mitarbeiter wurde entfernt.');
    }
}