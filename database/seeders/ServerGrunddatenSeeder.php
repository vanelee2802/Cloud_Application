<?php

namespace Database\Seeders;

use App\Models\NailStudio;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Grunddaten für den Server.
 * Darf bei jedem Deploy laufen – legt nichts doppelt an.
 */
class ServerGrunddatenSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Studio anlegen (nur wenn es noch nicht existiert)
        $studio = NailStudio::firstOrCreate(
            ['name' => 'Nagelstudio Mix and Match'],
            [
                'address'       => 'Musterstraße 1, 30159 Hannover',
                'opening_hours' => 'Mo–Fr 9–18 Uhr, Sa 10–14 Uhr',
            ]
        );

        // 2. Leistungen des Studios
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

        // 3. Studio allen Inhaberinnen als eigenes Studio zuordnen
        $owners = User::query()->where('role', 'owner')->get();

        foreach ($owners as $owner) {
            $relation = $owner->ownedStudios();

            if ($relation instanceof BelongsToMany) {
                $relation->syncWithoutDetaching([$studio->id]);
            } elseif (! $owner->ownedStudios()->whereKey($studio->id)->exists()) {
                $owner->ownedStudios()->save($studio);
            }
        }

        // 4. NEU: Inhaberinnen und Admins ohne direktes Studio bekommen dieses Studio
        User::query()
            ->whereIn('role', ['owner', 'admin'])
            ->whereNull('nail_studio_id')
            ->get()
            ->each(function (User $user) use ($studio) {
                $user->forceFill(['nail_studio_id' => $studio->id])->save();
            });

        // 5. Demo-Mitarbeiterinnen für die Präsentation (Login nicht möglich: zufälliges Passwort)
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