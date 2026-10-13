<?php

return [
    // Buchbare Uhrzeiten
    'times' => ['10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'],

    // So viele Minuten bleibt ein ausgewählter Termin reserviert
    'hold_minutes' => 3,

    // So viele Minuten hat die Kundin nach dem Abschicken Zeit zum Bezahlen
    'payment_minutes' => 30,

    // So viele Minuten hat die Kundin nach der Bestätigung eines Termins für heute Zeit zum Bezahlen
    'same_day_payment_minutes' => 60,
];