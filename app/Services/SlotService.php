<?php

namespace App\Services;

use App\Models\User;

class SlotService
{
    /**
     * Wie viele Termine pro Uhrzeit gleichzeitig möglich sind
     * = Anzahl Mitarbeiter dieses Studios.
     */
    public function capacity(int $studioId): int
    {
        return User::where('nail_studio_id', $studioId)
            ->whereIn('role', ['employee', 'admin'])
            ->count();
    }
}