<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::query()
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
            'users' => $users,
        ]);
    }
    
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],
        ]);

        $user->update($validated);

        return redirect()
            ->back()
            ->with('success', 'Benutzerdaten wurden aktualisiert.');
    }

}
