<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['invitation_id', 'photo_path', 'position'])]
class Photo extends Model
{
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
