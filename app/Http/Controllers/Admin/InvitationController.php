<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Template;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvitationController extends Controller
{
    private const STATUSES = ['pending', 'active', 'inactive'];

    private const SORTABLE = [
        'groom_name', 'owner_name', 'template_name', 'event_date', 'status', 'created_at',
    ];

    public function index(Request $request)
    {
        $sort = $request->query('sort', 'created_at');
        if (! in_array($sort, self::SORTABLE, true)) {
            $sort = 'created_at';
        }

        $direction = $request->query('direction', 'asc');
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Invitation::query()
            ->with(['user', 'template']);

        switch ($sort) {
            case 'groom_name':
                $query->orderBy('groom_name', $direction)->orderBy('bride_name', $direction);
                break;
            case 'owner_name':
                $query->orderBy(
                    User::select('name')->whereColumn('users.id', 'invitations.user_id'),
                    $direction,
                );
                break;
            case 'template_name':
                $query->orderBy(
                    Template::select('name')->whereColumn('templates.id', 'invitations.template_id'),
                    $direction,
                );
                break;
            case 'event_date':
                $query->orderBy('event_date', $direction);
                break;
            case 'status':
                $query->orderBy('status', $direction);
                break;
            default:
                $query->orderBy('created_at', $direction);
        }

        $query->orderBy('id', $direction);

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
                'sort' => $sort,
                'direction' => $direction,
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