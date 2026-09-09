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
            'preview' => $this->isPreview($invitation),
        ]);
    }

    public function render(string $slug, array $extra = [])
    {
        [$component, $invitation] = $this->resolve($slug);

        return Inertia::render($component, array_merge([
            'invitation' => $invitation,
            'wishes' => $this->visibleWishes($invitation),
            'preview' => $this->isPreview($invitation),
        ], $extra));
    }

    private function resolve(string $slug): array
    {
        $query = Invitation::with([
            'template',
            'melody',
            'events' => fn ($query) => $query->orderBy('position'),
            'photos' => fn ($query) => $query->orderBy('position'),
        ])->where('slug', $slug);

        $user = auth()->user();

        if ($user?->role !== 'admin') {
            $query->where(function ($query) use ($user) {
                $query->where('status', 'active');

                if ($user) {
                    $query->orWhere('user_id', $user->id);
                }
            });
        }

        $invitation = $query->firstOrFail();

        $component = 'Public/Templates/' . Str::studly($invitation->template->slug);

        if (! File::exists(resource_path("js/Pages/{$component}.jsx"))) {
            abort(404);
        }

        $invitation->setRelation('events', $invitation->events->map(fn ($event) => [
            'id' => $event->id,
            'name' => $event->name,
            'time' => $event->pivot->time,
        ]));

        return [$component, $invitation];
    }

    private function isPreview(Invitation $invitation): bool
    {
        return $invitation->status !== 'active';
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