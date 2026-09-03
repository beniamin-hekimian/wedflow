<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'file_path'])]
class Melody extends Model
{
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}
