<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Melody;
use App\Models\Photo;
use App\Models\Response;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class InvitationController extends Controller
{
    public function index()
    {
        $invitations = Invitation::with('template')
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Invitations/Index', [
            'invitations' => $invitations,
        ]);
    }

    public function create(Request $request)
    {
        $template = Template::findOrFail($request->query('template'));

        return Inertia::render('Invitations/Create', [
            'template' => $template,
            'melodies' => Melody::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $photoPaths = [];

        try {
            DB::transaction(function () use ($validated, $request, &$photoPaths) {
                $invitation = Invitation::create([
                    ...$validated,
                    'user_id' => Auth::id(),
                    'slug' => $this->generateUniqueSlug($validated['groom_name'], $validated['bride_name']),
                    'status' => 'pending',
                ]);

                $events = collect($validated['events'])
                    ->values()
                    ->map(fn ($event, $index) => [
                        'name' => $event['name'],
                        'time' => $event['time'],
                        'position' => $index,
                    ])
                    ->all();

                $invitation->events()->createMany($events);

                $photos = [];

                foreach ($request->file('photos', []) as $index => $photo) {
                    $path = $photo->store("invitations/{$invitation->id}", 'public');
                    $photoPaths[] = $path;
                    $photos[] = [
                        'photo_path' => $path,
                        'position' => $index,
                    ];
                }

                $invitation->photos()->createMany($photos);
            });
        } catch (Throwable $e) {
            foreach ($photoPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }

        return to_route('invitations.index')->with('success', 'Invitation created successfully.');
    }

    public function edit(Invitation $invitation)
    {
        $this->authorizeOwnership($invitation);

        return Inertia::render('Invitations/Edit', [
            'invitation' => $invitation->load(['template', 'melody', 'events', 'photos']),
            'template' => $invitation->template,
            'melodies' => Melody::all(),
        ]);
    }

    public function update(Request $request, Invitation $invitation)
    {
        $this->authorizeOwnership($invitation);

        $validated = $request->validate($this->rules());

        $photoPaths = [];

        try {
            DB::transaction(function () use ($validated, $request, $invitation, &$photoPaths) {
                $invitation->forceFill($this->editableFields($validated))->save();

                $invitation->events()->delete();
                $invitation->events()->createMany(
                    collect($validated['events'])
                        ->values()
                        ->map(fn ($event, $index) => [
                            'name' => $event['name'],
                            'time' => $event['time'],
                            'position' => $index,
                        ])
                        ->all(),
                );

                $this->syncPhotos($request, $validated, $invitation, $photoPaths);
            });
        } catch (Throwable $e) {
            foreach ($photoPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }

        return to_route('invitations.index')->with('success', 'Invitation updated successfully.');
    }

    public function destroy(Invitation $invitation)
    {
        $this->authorizeOwnership($invitation);

        Storage::disk('public')->deleteDirectory("invitations/{$invitation->id}");

        $invitation->delete();

        return to_route('invitations.index')->with('success', 'Invitation deleted successfully.');
    }

    public function responses(Request $request, Invitation $invitation)
    {
        $this->authorizeOwnership($invitation);

        $sortable = ['guest_name', 'is_attending', 'count', 'message', 'is_hidden', 'created_at'];

        $sort = $request->query('sort', 'created_at');
        if (! in_array($sort, $sortable, true)) {
            $sort = 'created_at';
        }

        $direction = $request->query('direction', 'desc');
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $rows = $invitation->responses()
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'total' => $invitation->responses()->count(),
            'attending' => $invitation->responses()->where('is_attending', true)->count(),
            'declined' => $invitation->responses()->where('is_attending', false)->count(),
            'confirmed_guests' => (int) $invitation->responses()->where('is_attending', true)->sum('count'),
            'wishes' => $invitation->responses()->whereNotNull('message')->count(),
        ];

        return Inertia::render('Invitations/Responses', [
            'invitation' => $invitation->load(['template', 'melody']),
            'rows' => $rows,
            'summary' => $summary,
            'filters' => [
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    public function toggleVisibility(Invitation $invitation, Response $response)
    {
        $this->authorizeOwnership($invitation);

        abort_unless($response->invitation_id === $invitation->id, 404);

        $response->is_hidden = ! $response->is_hidden;
        $response->save();

        return back()->with(
            'success',
            $response->is_hidden
                ? 'Wish hidden from guests.'
                : 'Wish is now visible to guests.',
        );
    }

    private function authorizeOwnership(Invitation $invitation): void
    {
        abort_unless($invitation->user_id === Auth::id(), 403);
    }

    private function rules(): array
    {
        return [
            'template_id' => ['required', 'exists:templates,id'],
            'melody_id' => ['required', 'exists:melodies,id'],
            'groom_name' => ['required', 'string', 'max:255'],
            'bride_name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'event_time' => ['required'],
            'venue_name' => ['required', 'string', 'max:255'],
            'venue_address' => ['required', 'string'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'events' => ['required', 'array', 'min:2', 'max:4'],
            'events.*.name' => ['required', 'string', 'max:255'],
            'events.*.time' => ['required', 'date_format:H:i'],
            'existing_photo_ids' => ['nullable', 'array'],
            'existing_photo_ids.*' => ['integer'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    private function editableFields(array $validated): array
    {
        return [
            'template_id' => $validated['template_id'],
            'melody_id' => $validated['melody_id'],
            'groom_name' => $validated['groom_name'],
            'bride_name' => $validated['bride_name'],
            'event_date' => $validated['event_date'],
            'event_time' => $validated['event_time'],
            'venue_name' => $validated['venue_name'],
            'venue_address' => $validated['venue_address'],
            'contact_phone' => $validated['contact_phone'] ?? null,
            'note' => $validated['note'] ?? null,
        ];
    }

    private function syncPhotos(Request $request, array $validated, Invitation $invitation, array &$photoPaths): void
    {
        $keptIds = array_map('intval', $validated['existing_photo_ids'] ?? []);
        $existingCount = count($keptIds);
        $newCount = count($request->file('photos', []));

        if ($existingCount + $newCount > 5) {
            throw ValidationException::withMessages([
                'photos' => 'You may have up to 5 photos in total.',
            ]);
        }

        if ($existingCount > 0 && $existingCount !== $invitation->photos()->whereIn('id', $keptIds)->count()) {
            throw ValidationException::withMessages([
                'photos' => 'One or more photos no longer belong to this invitation.',
            ]);
        }

        $removed = $invitation->photos()->whereNotIn('id', $keptIds)->get();

        foreach ($removed as $photo) {
            $photo->delete();
        }

        foreach ($invitation->photos()->whereIn('id', $keptIds)->orderBy('id')->get() as $photo) {
            $photo->update(['position' => array_search($photo->id, $keptIds, true)]);
        }

        $removedPaths = $removed->pluck('photo_path')->all();

        if ($removedPaths) {
            Storage::disk('public')->delete($removedPaths);
        }

        $position = count($keptIds);

        foreach ($request->file('photos', []) as $photo) {
            $path = $photo->store("invitations/{$invitation->id}", 'public');
            $photoPaths[] = $path;
            $invitation->photos()->create([
                'photo_path' => $path,
                'position' => $position++,
            ]);
        }
    }

    private function generateUniqueSlug(string $groom, string $bride): string
    {
        $groomSlug = Str::slug(strtok(trim($groom), ' ') ?: $groom) ?: 'invitation';
        $brideSlug = Str::slug(strtok(trim($bride), ' ') ?: $bride) ?: 'invitation';

        $base = "{$groomSlug}-{$brideSlug}";

        if (Invitation::where('slug', $base)->doesntExist()) {
            return $base;
        }

        $n = 2;

        do {
            $candidate = "{$base}-{$n}";
            $n++;
        } while (Invitation::where('slug', $candidate)->exists());

        return $candidate;
    }
}
