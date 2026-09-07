<?php

namespace App\Http\Controllers;

use App\Models\Response;
use Illuminate\Http\Request;

class RsvpController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $invitation = \App\Models\Invitation::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $request->validate([
            'guest_name' => ['required', 'string', 'max:255'],
            'is_attending' => ['required', 'boolean'],
            'count' => $request->boolean('is_attending')
                ? ['required', 'integer', 'min:1', 'max:10']
                : [],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $isAttending = $request->boolean('is_attending');
        $message = filled(trim((string) $request->input('message')))
            ? trim((string) $request->input('message'))
            : null;

        Response::create([
            'invitation_id' => $invitation->id,
            'guest_name' => $request->input('guest_name'),
            'is_attending' => $isAttending,
            'count' => $isAttending ? (int) $request->input('count') : 0,
            'message' => $message,
            'is_hidden' => false,
        ]);

        session()->flash('success', $isAttending
            ? "Thank you, we can't wait to see you!"
            : 'Thank you for letting us know.');

        return app(PublicInvitationController::class)->render($slug);
    }
}