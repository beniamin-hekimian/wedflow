<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total' => Invitation::count(),
                'active' => Invitation::where('status', 'active')->count(),
                'pending' => Invitation::where('status', 'pending')->count(),
                'users' => User::count(),
                'admins' => User::where('role', 'admin')->count(),
            ],
            'topInvitations' => Invitation::query()
                ->withCount('responses')
                ->whereRaw('(select count(*) from responses where responses.invitation_id = invitations.id) > 0')
                ->orderByDesc('responses_count')
                ->limit(3)
                ->get()
                ->map(fn (Invitation $invitation) => [
                    'id' => $invitation->id,
                    'slug' => $invitation->slug,
                    'groom_name' => $invitation->groom_name,
                    'bride_name' => $invitation->bride_name,
                    'event_date' => $invitation->event_date,
                    'status' => $invitation->status,
                    'responses_count' => $invitation->responses_count,
                ]),
        ]);
    }
}