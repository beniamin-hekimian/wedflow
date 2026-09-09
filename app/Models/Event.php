<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'sort_order'])]
class Event extends Model
{
    public function invitations(): BelongsToMany
    {
        return $this->belongsToMany(Invitation::class, 'event_invitation')
            ->withPivot(['time', 'position'])
            ->withTimestamps()
            ->orderBy('event_invitation.position');
    }
}