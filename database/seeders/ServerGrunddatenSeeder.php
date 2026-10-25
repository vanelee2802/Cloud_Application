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

    // Leistungen, die jedes Studio bekommt
    private const LEISTUNGEN = [
        ['name' => 'Maniküre + Design', 'price' => 25.00, 'duration_minutes' => 45],
        ['name' => 'Nur Maniküre',      'price' => 15.00, 'duration_minutes' => 30],
    ];

    public function run(): void
    {
        // 1. Hauptstudio anlegen (nur wenn es noch nicht existiert)
        $hauptStudio = NailStudio::firstOrCreate(
            ['name' => 'Nagelstudio Mix and Match'],
            [
                'address'       => 'Musterstraße 1, 30159 Hannover',
                'opening_hours' => 'Mo–Fr 9–18 Uhr, Sa 10–14 Uhr',
            ]
        );

        $this->leistungenAnlegen($hauptStudio);

        // 2. Hauptstudio gezielt der Haupt-Inhaberin zuordnen
        $hauptInhaberin = User::query()
            ->where('email', self::HAUPT_INHABERIN_EMAIL)
            ->first();

        if ($hauptInhaberin) {
            $hauptStudio->forceFill(['owner_id' => $hauptInhaberin->id])->save();

            if (! $hauptInhaberin->nail_studio_id) {
                $hauptInhaberin->forceFill(['nail_studio_id' => $hauptStudio->id])->save();
            }
        }

        // 3. Weitere Inhaberinnen bekommen ein eigenes, vollständiges Studio
        User::query()
            ->where('role', User::ROLE_OWNER)
            ->when($hauptInhaberin, fn ($query) => $query->where('id', '!=', $hauptInhaberin->id))
            ->get()
            ->each(function (User $owner) {
                $studioName = 'Mix and Match Filiale ' . $owner->name;

                // Vorhandenes Studio nutzen (z. B. altes "Teststudio von …"), sonst neu anlegen
                $studio = $owner->ownedStudios()->orderBy('id')->first();

                if (! $studio) {
                    $studio = $owner->ownedStudios()->create([
                        'name'          => $studioName,
                        'address'       => 'Lister Meile 10, 30161 Hannover',
                        'opening_hours' => 'Mo–Fr 10–19 Uhr, Sa 10–15 Uhr',
                    ]);
                } elseif (Str::startsWith($studio->name, 'Teststudio von')) {
                    // Altes Platzhalter-Studio umbenennen und vervollständigen
                    $studio->update([
                        'name'          => $studioName,
                        'address'       => 'Lister Meile 10, 30161 Hannover',
                        'opening_hours' => 'Mo–Fr 10–19 Uhr, Sa 10–15 Uhr',
                    ]);
                }

                $this->leistungenAnlegen($studio);

                // Inhaberin direkt ihrem eigenen Studio zuordnen
                $owner->forceFill(['nail_studio_id' => $studio->id])->save();

                // Eine Demo-Mitarbeiterin für dieses Studio
                $this->demoMitarbeiterin(
                    'Sara (Demo)',
                    'sara.demo.' . $owner->id . '@mixmatch.invalid',
                    $studio
                );
            });

        // 4. Admins ohne Studio bekommen das Hauptstudio
        User::query()
            ->where('role', User::ROLE_ADMIN)
            ->whereNull('nail_studio_id')
            ->get()
            ->each(function (User $admin) use ($hauptStudio) {
                $admin->forceFill(['nail_studio_id' => $hauptStudio->id])->save();
            });

        // 5. Demo-Mitarbeiterinnen für das Hauptstudio
        $this->demoMitarbeiterin('Lena (Demo)', 'lena.demo@mixmatch.invalid', $hauptStudio);
        $this->demoMitarbeiterin('Mia (Demo)',  'mia.demo@mixmatch.invalid',  $hauptStudio);
    }

    /** Leistungen für ein Studio anlegen (nichts doppelt) */
    private function leistungenAnlegen(NailStudio $studio): void
    {
        foreach (self::LEISTUNGEN as $leistung) {
            Service::firstOrCreate(
                [
                    'nail_studio_id' => $studio->id,
                    'name'           => $leistung['name'],
                ],
                [
                    'price'            => $leistung['price'],
                    'duration_minutes' => $leistung['duration_minutes'],
                ]
            );
        }
    }

    /** Demo-Mitarbeiterin anlegen (Login nicht möglich: zufälliges Passwort) */
    private function demoMitarbeiterin(string $name, string $email, NailStudio $studio): void
    {
        $employee = User::firstOrNew(['email' => $email]);

        if ($employee->exists) {
            return;
        }

        $employee->forceFill([
            'name'              => $name,
            'password'          => Hash::make(Str::random(40)),
            'role'              => User::ROLE_EMPLOYEE,
            'nail_studio_id'    => $studio->id,
            'email_verified_at' => now(),
        ])->save();
    }
}