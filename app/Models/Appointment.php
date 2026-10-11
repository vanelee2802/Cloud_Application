<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Events\AppointmentStatusChanged;
use Illuminate\Notifications\Notification as MailNotification;
use Illuminate\Support\Facades\Notification;

class Appointment extends Model
{
protected $fillable = [
    'user_id', 'nail_studio_id', 'service_id', 'employee_id',
    'design_id', 'date', 'time', 'status',
    'slot_key', 'hold_token', 'hold_expires_at',
    'payment_expires_at',
    'customer_name', 'customer_email',
    'admin_note',
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

        /**
     * Kundin benachrichtigen:
     * - E-Mail an customer_email (Gäste und Konten)
     * - zusätzlich im Kundenportal, wenn der Termin zu einem Konto gehört
     */
    public function notifyCustomer(MailNotification $mail, string $portalMessage): void
    {
        if ($this->customer_email) {
            Notification::route('mail', $this->customer_email)->notify($mail);
        }

        if ($this->user) {
            $this->user->notifications()->create([
                'type' => 'appointment_status_changed',
                'message' => $portalMessage,
                'read' => false,
            ]);

            broadcast(new AppointmentStatusChanged($this));
        }
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
        // Ersatztermine, die die Studioleitung angeboten hat
    public function alternatives()
    {
        return $this->hasMany(AppointmentAlternative::class);
    }
}