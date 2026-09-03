<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'template_id',
    'melody_id',
    'slug',
    'groom_name',
    'bride_name',
    'groom_parents',
    'bride_parents',
    'event_date',
    'event_time',
    'venue_name',
    'venue_address',
    'welcome_message',
    'contact_name',
    'contact_phone',
    'note',
    'status',
    'paid_at',
])]
class Invitation extends Model
{
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function melody(): BelongsTo
    {
        return $this->belongsTo(Melody::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    public function wishes(): HasMany
    {
        return $this->hasMany(Wish::class);
    }
}
