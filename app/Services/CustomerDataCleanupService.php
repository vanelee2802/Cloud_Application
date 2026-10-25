<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CustomerDataCleanupService
{
    // So lange darf kein Termin stattgefunden haben, bevor bereinigt wird
    private const INACTIVE_MONTHS = 24;

    // Aufbewahrungsfrist für abgeschlossene Termine (Nachweis für die Buchhaltung)
    private const RETENTION_YEARS = 8;

    /* ───────────────────────── Hilfs-Filter ───────────────────────── */

    private function inactiveCutoff(): string
    {
        return now()->subMonths(self::INACTIVE_MONTHS)->toDateString();
    }

    /** Termin ist ein aufbewahrungspflichtiger Nachweis */
    private function isRecord(Builder $q): Builder
    {
        return $q->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subYears(self::RETENTION_YEARS));
    }

    /** Termin ist kein Nachweis (nie abgeschlossen oder Frist abgelaufen) */
    private function isNotRecord(Builder $q): Builder
    {
        return $q->where(function (Builder $q) {
            $q->whereNull('completed_at')
                ->orWhere('completed_at', '<', now()->subYears(self::RETENTION_YEARS));
        });
    }

    /** Termin enthält noch Name oder E-Mail */
    private function hasPersonalData(Builder $q): Builder
    {
        return $q->where(function (Builder $q) {
            $q->whereNotNull('customer_name')
                ->orWhereNotNull('customer_email');
        });
    }

    /* ─────────────────────── Wer ist betroffen? ─────────────────────── */

    /**
     * Kundenkonten mit Terminen nur in den eigenen Studios
     * und ohne Termin in den letzten X Monaten / in der Zukunft.
     */
    private function candidateUsers(string $mode, array $studioIds): Builder
    {
        $cutoff = $this->inactiveCutoff();

        $query = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->where('created_at', '<', $cutoff)
            ->whereHas('appointments', fn (Builder $q) => $q->whereIn('nail_studio_id', $studioIds))
            ->whereDoesntHave('appointments', fn (Builder $q) => $q->whereNotIn('nail_studio_id', $studioIds))
            ->whereDoesntHave('appointments', fn (Builder $q) => $q->where('date', '>=', $cutoff));

        if ($mode === 'anonymize') {
            return $query->whereNull('anonymized_at');
        }

        // Lösch-Modus: auch bereits anonymisierte Konten, deren Nachweisfrist abgelaufen ist
        return $query->where(function (Builder $q) {
            $q->whereNull('anonymized_at')
                ->orWhereDoesntHave('appointments', fn (Builder $a) => $this->isRecord($a));
        });
    }

    /** Alte Gast-Termine (ohne Konto) in den eigenen Studios */
    private function guestScope(array $studioIds): Builder
    {
        return Appointment::query()
            ->whereNull('user_id')
            ->whereIn('nail_studio_id', $studioIds)
            ->where('date', '<', $this->inactiveCutoff());
    }

    /** Davon nur die, die kein Nachweis sind */
    private function guestAppointments(array $studioIds): Builder
    {
        return $this->guestScope($studioIds)
            ->where(fn (Builder $q) => $this->isNotRecord($q));
    }

    /* ─────────────────────────── Vorschau ─────────────────────────── */

    public function preview(string $mode, array $studioIds): array
    {
        $users = $this->candidateUsers($mode, $studioIds);

        if ($mode === 'anonymize') {
            return [
                'mode' => $mode,
                'users_deleted' => 0,
                'users_anonymized' => $users->count(),
                'guest_appointments' => $this->guestAppointments($studioIds)
                    ->where(fn (Builder $q) => $this->hasPersonalData($q))
                    ->count(),
            ];
        }

        return [
            'mode' => $mode,
            'users_deleted' => (clone $users)
                ->whereDoesntHave('appointments', fn (Builder $q) => $this->isRecord($q))
                ->count(),
            'users_anonymized' => (clone $users)
                ->whereHas('appointments', fn (Builder $q) => $this->isRecord($q))
                ->count(),
            'guest_appointments' => $this->guestAppointments($studioIds)->count(),
        ];
    }

    /* ────────────────────────── Ausführung ────────────────────────── */

    public function run(string $mode, array $studioIds): array
    {
        $result = [
            'mode' => $mode,
            'users_deleted' => 0,
            'users_anonymized' => 0,
            'guest_appointments' => 0,
        ];

        DB::transaction(function () use ($mode, $studioIds, &$result) {
            $this->candidateUsers($mode, $studioIds)
                ->withExists(['appointments as has_records' => fn (Builder $q) => $this->isRecord($q)])
                ->chunkById(100, function ($users) use ($mode, &$result) {
                    foreach ($users as $user) {
                        $this->deleteCartAndNotifications($user);

                        if ($mode === 'delete' && ! $user->has_records) {
                            $this->deleteCustomer($user);
                            $result['users_deleted']++;
                            continue;
                        }

                        if ($mode === 'delete') {
                            $this->deleteNonRecordData($user);
                        }

                        $this->anonymizeCustomer($user);
                        $result['users_anonymized']++;
                    }
                });

            $result['guest_appointments'] = $this->cleanGuestAppointments($mode, $studioIds);
        });

        Log::info('Kundendaten-Bereinigung ausgeführt', $result + [
            'studio_ids' => $studioIds,
            'by_user_id' => auth()->id(),
        ]);

        return $result;
    }

    /* ─────────────────────── Einzelne Schritte ─────────────────────── */

    private function deleteCartAndNotifications(User $user): void
    {
        $user->cartItems()->delete();
        $user->notifications()->delete();
    }

     /** Konto komplett entfernen (nur wenn keine Nachweise existieren) */
    private function deleteCustomer(User $user): void
    {
        // Reihenfolge wichtig: Termine zeigen auf Designs (on delete: no action)
        $user->appointments()->delete();
        // falls es eine Bewertungs-Tabelle gibt, hier ergänzen, z. B.:
        // $user->reviews()->delete();
        $user->designs()->delete();

        $this->deleteAvatarFile($user);
        $this->logoutEverywhere($user);
        $user->delete();
    }

    /** Alles löschen, was kein Nachweis ist – Nachweise bleiben */
    private function deleteNonRecordData(User $user): void
    {
        $user->appointments()
            ->where(fn (Builder $q) => $this->isNotRecord($q))
            ->delete();

        // Nur Designs löschen, die in keinem (verbleibenden) Termin verwendet werden
        $usedDesignIds = Appointment::query()
            ->whereNotNull('design_id')
            ->select('design_id');

        $user->designs()->whereNotIn('id', $usedDesignIds)->delete();
    }

       /** Konto anonymisieren, Nachweise behalten */
    private function anonymizeCustomer(User $user): void
    {
        // Name/E-Mail nur in Terminen entfernen, die kein Nachweis sind
        $user->appointments()
            ->where(fn (Builder $q) => $this->isNotRecord($q))
            ->update(['customer_name' => null, 'customer_email' => null]);

        // Interne Notizen werden für keinen Nachweis gebraucht
        $user->appointments()->update(['admin_note' => null]);

        $this->deleteAvatarFile($user);

        $user->forceFill([
            'name' => 'Anonymisierte Kundin #' . $user->id,
            'email' => 'anonym-' . $user->id . '@example.invalid',
            'password' => Str::random(64), // wird durch den 'hashed'-Cast gehasht
            'remember_token' => null,
            'email_verified_at' => null,
            'avatar' => null,
            'pm_type' => null,
            'pm_last_four' => null,
            'trial_ends_at' => null,
            // stripe_id bleibt: Verknüpfung zu Zahlungsnachweisen bei Stripe
            'anonymized_at' => $user->anonymized_at ?? now(),
        ])->save();

        $this->logoutEverywhere($user);
    }

    /** Gast-Termine ohne Konto bereinigen */
    private function cleanGuestAppointments(string $mode, array $studioIds): int
    {
        // Interne Notizen in allen alten Gast-Terminen entfernen
        $this->guestScope($studioIds)
            ->whereNotNull('admin_note')
            ->update(['admin_note' => null]);

        if ($mode === 'delete') {
            return $this->guestAppointments($studioIds)->delete();
        }

        return $this->guestAppointments($studioIds)
            ->where(fn (Builder $q) => $this->hasPersonalData($q))
            ->update(['customer_name' => null, 'customer_email' => null]);
    }

        /** Hochgeladenes Profilbild löschen (externe URLs wie Google werden nur geleert) */
    private function deleteAvatarFile(User $user): void
    {
        $avatar = $user->avatar;

        if ($avatar && ! Str::startsWith($avatar, ['http://', 'https://'])) {
            Storage::disk('public')->delete($avatar);
        }
    }

    private function logoutEverywhere(User $user): void
    {
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
    }
}