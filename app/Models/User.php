<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

        public const ROLE_OWNER    = 'owner';
    public const ROLE_ADMIN    = 'admin';
    public const ROLE_EMPLOYEE = 'employee';
    public const ROLE_CUSTOMER = 'customer';

 

    public function ownedStudios(): HasMany
    {
        return $this->hasMany(\App\Models\NailStudio::class, 'owner_id');
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function manageableStudioIds(): array
    {
        if ($this->isOwner()) {
            return $this->ownedStudios()->pluck('id')->all();
        }

        if ($this->isAdmin() && $this->nail_studio_id) {
            return [$this->nail_studio_id];
        }

        return [];
    }
}