<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['invitation_id', 'name', 'time', 'position'])]
class Event extends Model
{
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
