<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Schicht einer Mitarbeiterin an einem Wochentag.
 * weekday: 1 = Montag … 7 = Sonntag
 */
class EmployeeShift extends Model
{
    public const WEEKDAYS = [
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'thursday',
        5 => 'friday',
        6 => 'saturday',
        7 => 'sunday',
    ];

    protected $fillable = [
        'user_id',
        'weekday',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'weekday' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Schichten einer Person als { monday: { start, end }, … }
     * Tage ohne Schicht fehlen (= frei).
     */
    public static function mapFor(User $user): array
    {
        return $user->shifts
            ->mapWithKeys(fn (self $shift) => [
                self::WEEKDAYS[$shift->weekday] => [
                    'start' => substr($shift->start_time, 0, 5),
                    'end'   => substr($shift->end_time, 0, 5),
                ],
            ])
            ->all();
    }
}
