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
}