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
        [$component, $invitation] = $this->resolve($slug);

        return Inertia::render($component, [
            'invitation' => $invitation,
            'wishes' => $this->visibleWishes($invitation),
        ]);
    }

    public function render(string $slug, array $extra = [])
    {
        [$component, $invitation] = $this->resolve($slug);

        return Inertia::render($component, array_merge([
            'invitation' => $invitation,
            'wishes' => $this->visibleWishes($invitation),
        ], $extra));
    }

    private function resolve(string $slug): array
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

        return [$component, $invitation];
    }

    private function visibleWishes(Invitation $invitation)
    {
        return $invitation->responses()
            ->whereNotNull('message')
            ->where('is_hidden', false)
            ->orderByDesc('created_at')
            ->select(['id', 'guest_name', 'message', 'created_at'])
            ->get();
    }
}