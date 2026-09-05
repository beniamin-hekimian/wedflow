<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Melody;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        $validated = $request->validate([
            'template_id' => ['required', 'exists:templates,id'],
            'melody_id' => ['required', 'exists:melodies,id'],
            'groom_name' => ['required', 'string', 'max:255'],
            'bride_name' => ['required', 'string', 'max:255'],
            'groom_parents' => ['required', 'string', 'max:255'],
            'bride_parents' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'event_time' => ['required'],
            'venue_name' => ['required', 'string', 'max:255'],
            'venue_address' => ['required', 'string'],
            'welcome_message' => ['nullable', 'string'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'events' => ['required', 'array', 'min:2', 'max:4'],
            'events.*.name' => ['required', 'string', 'max:255'],
            'events.*.time' => ['required', 'date_format:H:i'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

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

    private function generateUniqueSlug(string $groom, string $bride): string
    {
        $groomSlug = Str::slug($groom) ?: 'invitation';
        $brideSlug = Str::slug($bride) ?: 'invitation';

        $candidates = [
            "{$groomSlug}-{$brideSlug}",
            "{$groomSlug}-&-{$brideSlug}",
            "{$groomSlug}-and-{$brideSlug}",
            "{$groomSlug}-with-{$brideSlug}",
        ];

        foreach ($candidates as $candidate) {
            if (Invitation::where('slug', $candidate)->doesntExist()) {
                return $candidate;
            }
        }

        $n = 2;

        do {
            $candidate = "{$groomSlug}-{$brideSlug}-{$n}";
            $n++;
        } while (Invitation::where('slug', $candidate)->exists());

        return $candidate;
    }
}
