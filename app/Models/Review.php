<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'appointment_id',
        'nail_studio_id',
        'rating',
        'comment',
        'author_name',
        'is_hidden',
    ];

    protected function casts(): array
    {
        return [
            'rating'    => 'integer',
            'is_hidden' => 'boolean',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function nailStudio(): BelongsTo
    {
        return $this->belongsTo(NailStudio::class);
    }
}
