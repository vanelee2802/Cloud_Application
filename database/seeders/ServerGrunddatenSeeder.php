<?php

namespace Database\Seeders;

use App\Models\NailStudio;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Grunddaten für den Server.
 * Darf bei jedem Deploy laufen – legt nichts doppelt an.
 */
class ServerGrunddatenSeeder extends Seeder
{
    // Inhaberin des Hauptstudios
    private const HAUPT_INHABERIN_EMAIL = 'oetken45@gmail.com';

    public function run(): void
    {
        // 1. Hauptstudio anlegen (nur wenn es noch nicht existiert)
        $studio = NailStudio::firstOrCreate(
            ['name' => 'Nagelstudio Mix and Match'],
            [
                'address'       => 'Musterstraße 1, 30159 Hannover',
                'opening_hours' => 'Mo–Fr 9–18 Uhr, Sa 10–14 Uhr',
            ]
        );

        // 2. Leistungen des Hauptstudios
        $services = [
            ['name' => 'Maniküre + Design', 'price' => 25.00, 'duration_minutes' => 45],
            ['name' => 'Nur Maniküre',      'price' => 15.00, 'duration_minutes' => 30],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(
                [
                    'nail_studio_id' => $studio->id,
                    'name'           => $service['name'],
                ],
                [
                    'price'            => $service['price'],
                    'duration_minutes' => $service['duration_minutes'],
                ]
            );
        }

        // 3. Hauptstudio gezielt der Haupt-Inhaberin zuordnen (owner_id)
        $hauptInhaberin = User::query()
            ->where('email', self::HAUPT_INHABERIN_EMAIL)
            ->first();

        if ($hauptInhaberin) {
            $studio->forceFill(['owner_id' => $hauptInhaberin->id])->save();
        }

        // 4. Weitere Inhaberinnen ohne eigenes Studio bekommen ein eigenes Teststudio
        User::query()
            ->where('role', User::ROLE_OWNER)
            ->when($hauptInhaberin, fn ($query) => $query->where('id', '!=', $hauptInhaberin->id))
            ->get()
            ->each(function (User $owner) {
                if ($owner->ownedStudios()->exists()) {
                    return;
                }

                $owner->ownedStudios()->create([
                    'name'          => 'Teststudio von ' . $owner->name,
                    'address'       => 'Musterstraße 2, 30159 Hannover',
                    'opening_hours' => 'Mo–Fr 9–18 Uhr',
                ]);
            });

        // 5. Inhaberinnen ohne direktes Studio bekommen ihr erstes eigenes Studio
        User::query()
            ->where('role', User::ROLE_OWNER)
            ->whereNull('nail_studio_id')
            ->get()
            ->each(function (User $owner) {
                $ersteStudioId = $owner->ownedStudios()->orderBy('id')->value('id');

                if ($ersteStudioId) {
                    $owner->forceFill(['nail_studio_id' => $ersteStudioId])->save();
                }
            });

        // 6. Admins ohne Studio bekommen das Hauptstudio
        User::query()
            ->where('role', User::ROLE_ADMIN)
            ->whereNull('nail_studio_id')
            ->get()
            ->each(function (User $admin) use ($studio) {
                $admin->forceFill(['nail_studio_id' => $studio->id])->save();
            });

        // 7. Demo-Mitarbeiterinnen für die Präsentation (Login nicht möglich: zufälliges Passwort)
        $demoEmployees = [
            ['name' => 'Lena (Demo)', 'email' => 'lena.demo@mixmatch.invalid'],
            ['name' => 'Mia (Demo)',  'email' => 'mia.demo@mixmatch.invalid'],
        ];

        foreach ($demoEmployees as $data) {
            $employee = User::firstOrNew(['email' => $data['email']]);

            if (! $employee->exists) {
                $employee->forceFill([
                    'name'              => $data['name'],
                    'password'          => Hash::make(Str::random(40)),
                    'role'              => User::ROLE_EMPLOYEE,
                    'nail_studio_id'    => $studio->id,
                    'email_verified_at' => now(),
                ])->save();
            }
        }
    }
}