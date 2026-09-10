<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'nail_studio_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Falls dieser Nutzer ein Mitarbeiter ist: zu welchem Studio er gehört
    public function nailStudio()
    {
        return $this->belongsTo(NailStudio::class);
    }

    // Designs, die dieser Nutzer als Kunde erstellt hat
    public function designs()
    {
        return $this->hasMany(Design::class);
    }

    // Termine, die dieser Nutzer als Kunde gebucht hat
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // Termine, die diesem Nutzer als Mitarbeiter zugewiesen wurden
    public function assignedAppointments()
    {
        return $this->hasMany(Appointment::class, 'employee_id');
    }

    // Warenkorb-Einträge dieses Nutzers
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    // Benachrichtigungen für diesen Nutzer
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}