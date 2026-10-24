<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Mitarbeiterin hinzufügen.
     *
     * Admin (Schichtleitung): bekommt automatisch das eigene Studio.
     * Inhaberin: wählt eines ihrer Studios aus (Feld studio_id).
     *
     * Gibt es die E-Mail schon (z. B. Kundin mit Google-Login),
     * wird das bestehende Konto zur Mitarbeiterin gemacht.
     */
    public function store(Request $request): RedirectResponse
    {
        $actor   = $request->user();
        $allowed = $actor->manageableStudioIds();

        $validated = $request->validate(
            [
                'name'      => 'required|string|max:255',
                'email'     => 'required|email|max:255',
                'studio_id' => [
                    Rule::requiredIf($actor->isOwner()),
                    'nullable',
                    Rule::in($allowed),
                ],
            ],
            [
                'name.required'      => 'Bitte gib einen Namen ein.',
                'email.required'     => 'Bitte gib eine E-Mail-Adresse ein.',
                'email.email'        => 'Bitte gib eine gültige E-Mail-Adresse ein.',
                'studio_id.required' => 'Bitte wähle ein Studio aus.',
                'studio_id.in'       => 'Dieses Studio darfst du nicht verwalten.',
            ]
        );

        // Admin: immer das eigene Studio, egal was mitgeschickt wird
        $studioId = $actor->isOwner()
            ? (int) $validated['studio_id']
            : $actor->nail_studio_id;

        if (! $studioId) {
            return back()->withErrors([
                'email' => 'Deinem Konto ist noch kein Studio zugeordnet. Bitte wende dich an die Inhaberin.',
            ]);
        }

        $email = Str::lower(trim($validated['email']));
        $user  = User::where('email', $email)->first();

        if ($user && in_array($user->role, [User::ROLE_OWNER, User::ROLE_ADMIN], true)) {
            return back()->withErrors([
                'email' => 'Diese E-Mail gehört zu einem Leitungs-Konto.',
            ]);
        }

        if ($user?->role === User::ROLE_EMPLOYEE) {
            return back()->withErrors([
                'email' => $user->nail_studio_id == $studioId
                    ? 'Diese Person ist bereits Mitarbeiterin in diesem Studio.'
                    : 'Diese Person arbeitet bereits in einem anderen Studio.',
            ]);
        }

        if ($user) {
            $user->update([
                'role'           => User::ROLE_EMPLOYEE,
                'nail_studio_id' => $studioId,
            ]);
        } else {
            User::create([
                'name'           => trim($validated['name']),
                'email'          => $email,
                'password'       => bcrypt(Str::random(40)), // Login läuft über Google
                'role'           => User::ROLE_EMPLOYEE,
                'nail_studio_id' => $studioId,
            ]);
        }

        return back()->with('success', 'Mitarbeiterin wurde hinzugefügt.');
    }

    /**
     * Mitarbeiterin entfernen. Das Konto bleibt als Kundin erhalten,
     * damit alte Termine nicht verloren gehen.
     */
    public function destroy(Request $request, User $employee): RedirectResponse
    {
        abort_unless($employee->role === User::ROLE_EMPLOYEE, 404);
        abort_unless(
            in_array($employee->nail_studio_id, $request->user()->manageableStudioIds()),
            403
        );

        $employee->update([
            'role'           => User::ROLE_CUSTOMER,
            'nail_studio_id' => null,
        ]);

        return back()->with('success', 'Mitarbeiterin wurde entfernt.');
    }
}