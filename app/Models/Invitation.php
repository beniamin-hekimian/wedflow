<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'template_id',
    'melody_id',
    'slug',
    'groom_name',
    'bride_name',
    'event_date',
    'event_time',
    'venue_name',
    'venue_address',
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

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_invitation')
            ->withPivot(['time', 'position'])
            ->withTimestamps()
            ->orderBy('event_invitation.position');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }
}
