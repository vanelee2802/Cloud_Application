<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Benutzerverwaltung – nur für die Inhaberin (role = owner).
 * Sie sieht die Leute ihrer eigenen Studios und alle Kundinnen.
 */
class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $owner     = $request->user();
        $studioIds = $owner->manageableStudioIds();

        $users = User::query()
        ->whereNull('anonymized_at')
            ->where(function ($q) use ($owner, $studioIds) {
                $q->where('id', $owner->id)
                  ->orWhereIn('nail_studio_id', $studioIds)
                  ->orWhere('role', User::ROLE_CUSTOMER);
            })
            ->with('nailStudio:id,name')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
                'role',
                'nail_studio_id',
                'created_at',
            ]);

        return Inertia::render('UserManagement', [
            'users'   => $users,
            'studios' => $owner->ownedStudios()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $owner     = $request->user();
        $studioIds = $owner->manageableStudioIds();

        // Andere Inhaberinnen und Leute aus fremden Studios sind tabu
        abort_if($user->isOwner() && $user->id !== $owner->id, 403);
        abort_if(
            $user->nail_studio_id && ! in_array($user->nail_studio_id, $studioIds),
            403
        );

        $validated = $request->validate(
            [
                'name'  => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($user->id),
                ],

                // Optional: nur wenn das Formular sie mitschickt
                'role' => [
                    'sometimes',
                    Rule::in([User::ROLE_ADMIN, User::ROLE_EMPLOYEE, User::ROLE_CUSTOMER]),
                ],
                'nail_studio_id' => [
                    Rule::requiredIf(fn () => in_array(
                        $request->input('role'),
                        [User::ROLE_ADMIN, User::ROLE_EMPLOYEE],
                        true
                    )),
                    'nullable',
                    Rule::in($studioIds),
                ],
            ],
            [
                'nail_studio_id.required' => 'Bitte wähle ein Studio aus.',
                'nail_studio_id.in'       => 'Dieses Studio gehört nicht zu dir.',
            ]
        );

        $data = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        // Rolle der Inhaberin selbst wird hier nie geändert
        if (array_key_exists('role', $validated) && ! $user->isOwner()) {
            $data['role']           = $validated['role'];
            $data['nail_studio_id'] = $validated['role'] === User::ROLE_CUSTOMER
                ? null
                : (int) $validated['nail_studio_id'];
        }

        $user->update($data);

        return back()->with('success', 'Benutzerdaten wurden aktualisiert.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
{
    $owner = $request->user();

    // Nur Studioinhaber dürfen Benutzer entfernen.
    abort_unless($owner->isOwner(), 403);

    // Kunden und Mitarbeiter dürfen entfernt werden,
    // andere Rollen bleiben geschützt.
    abort_unless(
        in_array($user->role, [User::ROLE_CUSTOMER, User::ROLE_EMPLOYEE], true),
        403
    );

    // Mitarbeiter nicht löschen, damit Termine und zugehörige
    // Datensätze nicht versehentlich verloren gehen.
    if ($user->role === User::ROLE_EMPLOYEE) {
        $user->nail_studio_id = null;
        $user->role = User::ROLE_CUSTOMER;
        $user->save();

        return back()->with(
            'success',
            'Der Mitarbeiter wurde aus deinem Studio entfernt.'
        );
    }

    // Kundenkonto anonymisieren, statt Termine und Designs
    // durch ein echtes Löschen möglicherweise zu verlieren.
    if ($user->anonymized_at !== null) {
        return back()->with(
            'success',
            'Dieses Kundenkonto wurde bereits anonymisiert.'
        );
    }

    \Illuminate\Support\Facades\DB::transaction(function () use ($user) {
        $user->appointments()->update([
            'customer_name' => 'Gelöschter Kunde',
            'customer_email' => null,
        ]);

        $user->name = 'Gelöschter Kunde';
        $user->email = 'deleted-' . $user->id . '@anonymized.invalid';
        $user->email_verified_at = null;
        $user->password = \Illuminate\Support\Str::random(64);
        $user->remember_token = null;
        $user->anonymized_at = now();
        $user->save();
    });

    return back()->with(
        'success',
        'Das Kundenkonto wurde anonymisiert.'
    );
}
}