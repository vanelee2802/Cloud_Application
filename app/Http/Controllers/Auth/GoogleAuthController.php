<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    // Schritt 1: Leitet den Nutzer zu Google weiter
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // Schritt 2: Google schickt den Nutzer hierher zurück
    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        // Prüfen, ob die Google-E-Mail bereits existiert
        $user = User::where('email', $googleUser->getEmail())->first();

        // Standardrolle für neue Benutzer
        $role = 'customer';

        // Rolle anhand der hinterlegten Google-E-Mail bestimmen
        if ($googleUser->getEmail() === env('GOOGLE_ADMIN_EMAIL')) {
            $role = 'admin';
        } elseif ($googleUser->getEmail() === env('GOOGLE_EMPLOYEE_EMAIL')) {
            $role = 'employee';
        }

        // Wenn die E-Mail noch nicht existiert,
        // neuen Benutzer mit der passenden Rolle erstellen
        if (!$user) {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => Str::random(24),
                'role' => $role,
                'email_verified_at' => now(),
            ]);
        }

        // Vorhandenen oder neu erstellten Benutzer einloggen
        Auth::login($user);

        // Je nach gespeicherter Rolle weiterleiten
        if ($user->role === 'admin') {
            return redirect('/StudioDashboard');
        }

        if ($user->role === 'employee') {
            return redirect('/Employee');
        }

        return redirect('/DesignEditor');
    }
}