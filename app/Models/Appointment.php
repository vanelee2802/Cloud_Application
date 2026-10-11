<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
  protected $fillable = [
    'user_id', 'nail_studio_id', 'service_id', 'employee_id',
    'design_id', 'date', 'time', 'status',
    'slot_key', 'hold_token', 'hold_expires_at',
    'payment_expires_at',
];

    protected $hidden = [
        'slot_key',
        'hold_token',
    ];

    protected $casts = [
    'hold_expires_at' => 'datetime',
    'payment_expires_at' => 'datetime',
];
        /**
     * Baut den Schlüssel für einen Platz, z. B. "1|2026-10-20|14:00|2".
     */
    public static function slotKey(int $studioId, string $date, string $time, int $seat): string
    {
        $time = substr($time, 0, 5); // "14:00:00" → "14:00"

        return "{$studioId}|{$date}|{$time}|{$seat}";
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nailStudio()
    {
        return $this->belongsTo(NailStudio::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function design()
    {
        return $this->belongsTo(Design::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}