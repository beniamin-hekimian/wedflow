<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PublicInvitationController extends Controller
{
    public function __invoke(string $slug)
    {
        $invitation = Invitation::with([
            'template',
            'melody',
            'events' => fn ($query) => $query->orderBy('position'),
            'photos' => fn ($query) => $query->orderBy('position'),
        ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $component = 'Public/Templates/' . Str::studly($invitation->template->slug);

        if (! File::exists(resource_path("js/Pages/{$component}.jsx"))) {
            abort(404);
        }

        return Inertia::render($component, [
            'invitation' => $invitation,
        ]);
    }
}