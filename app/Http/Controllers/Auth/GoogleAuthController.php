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
        return Socialite::driver('google')->stateless()->redirect();
    }

    // Schritt 2: Google schickt den Nutzer hierher zurück
    public function callback()
    {
        try {

            $googleUser = Socialite::driver('google')->stateless()->user();

        } catch (\Throwable $e) {

            // Fehler ins Log schreiben, Nutzer bekommt eine verständliche Meldung
            report($e);

            return redirect('/')
                ->with('error', 'Die Anmeldung mit Google hat nicht geklappt. Bitte versuche es erneut.');

        }        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'password' => Str::random(24), // zufälliges Passwort, da Login über Google läuft
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        );

        Auth::login($user);

        return redirect('/dashboard');
    }
}