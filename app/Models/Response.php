<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['invitation_id', 'guest_name', 'is_attending', 'count', 'message', 'is_hidden'])]
class Response extends Model
{
    protected function casts(): array
    {
        return [
            'is_attending' => 'boolean',
            'is_hidden' => 'boolean',
            'count' => 'integer',
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}