<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'thumbnail_path', 'intro_video_path'])]
class Template extends Model
{
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}
