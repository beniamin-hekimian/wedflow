<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvitationController extends Controller
{
    private const STATUSES = ['pending', 'active', 'inactive'];

    public function index(Request $request)
    {
        $query = Invitation::query()
            ->with(['user', 'template'])
            ->orderByDesc('created_at');

        if ($request->filled('status') && in_array($request->string('status')->toString(), self::STATUSES)) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->string('search') . '%';

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('groom_name', 'like', $search)
                    ->orWhere('bride_name', 'like', $search)
                    ->orWhere('venue_name', 'like', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    });
            });
        }

        $invitations = $query->paginate(10)->appends($request->query());

        return Inertia::render('Admin/Invitations/Index', [
            'invitations' => $invitations,
            'filters' => [
                'status' => $request->query('status'),
                'search' => $request->query('search'),
            ],
            'statusCounts' => $this->statusCounts(),
        ]);
    }

    public function updateStatus(Request $request, Invitation $invitation)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::STATUSES)],
        ]);

        $invitation->update(['status' => $validated['status']]);

        return back()->with(
            'success',
            "Invitation #{$invitation->id} status updated to " . ucfirst($validated['status']) . '.',
        );
    }

    private function statusCounts(): array
    {
        $counts = Invitation::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $result = ['total' => Invitation::count()];

        foreach (self::STATUSES as $status) {
            $result[$status] = $counts[$status] ?? 0;
        }

        return $result;
    }
}