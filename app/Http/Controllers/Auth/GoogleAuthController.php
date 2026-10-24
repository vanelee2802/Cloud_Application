<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    // Schritt 1: Leitet den Nutzer zu Google weiter
    public function redirect()
    {
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    // Schritt 2: Google schickt den Nutzer hierher zurück
    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            // Fehler ins Log schreiben, Nutzer bekommt eine verständliche Meldung
            report($e);

            return redirect('/')
                ->with('error', 'Die Anmeldung mit Google hat nicht geklappt. Bitte versuche es erneut.');
        }

        // Bestehendes Konto finden oder neues als Kundin anlegen.
        // Die Rolle eines bestehenden Kontos bleibt unverändert –
        // sie wird ausschließlich über die Benutzerverwaltung vergeben.
        $user = User::firstOrCreate(
            ['email' => Str::lower($googleUser->getEmail())],
            [
                'name'              => $googleUser->getName(),
                'password'          => bcrypt(Str::random(40)), // Login läuft über Google
                'role'              => User::ROLE_CUSTOMER,
                'email_verified_at' => now(),
            ]
        );

        Auth::login($user);
        $request->session()->regenerate();

        // Je nach gespeicherter Rolle weiterleiten
        if (in_array($user->role, [User::ROLE_OWNER, User::ROLE_ADMIN], true)) {
            return redirect('/Studioverwaltung');
        }

        if ($user->role === User::ROLE_EMPLOYEE) {
            return redirect('/StudioDashboard');
        }

        return redirect('/DesignEditor');
    }
}