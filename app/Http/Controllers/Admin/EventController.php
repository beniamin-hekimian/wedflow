<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    private const USAGE_FILTERS = ['in-use', 'unused'];

    private const SORTABLE = [
        'name', 'sort_order', 'invitations_count', 'created_at',
    ];

    public function index(Request $request)
    {
        $usage = $request->query('usage');
        if (! in_array($usage, self::USAGE_FILTERS, true)) {
            $usage = null;
        }

        $sort = $request->query('sort', 'sort_order');
        if (! in_array($sort, self::SORTABLE, true)) {
            $sort = 'sort_order';
        }

        $direction = $request->query('direction', 'asc');
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Event::query()->withCount('invitations');

        switch ($sort) {
            case 'name':
                $query->orderBy('name', $direction);
                break;
            case 'sort_order':
                $query->orderBy('sort_order', $direction);
                break;
            case 'invitations_count':
                $query->orderBy(
                    Invitation::query()
                        ->join('event_invitation', 'event_invitation.invitation_id', '=', 'invitations.id')
                        ->whereColumn('event_invitation.event_id', 'events.id')
                        ->selectRaw('count(*)'),
                    $direction,
                );
                break;
            default:
                $query->orderBy('sort_order', $direction);
        }

        $query->orderBy('id', $direction);

        if ($usage === 'in-use') {
            $query->whereHas('invitations');
        } elseif ($usage === 'unused') {
            $query->whereDoesntHave('invitations');
        }

        if ($request->filled('search')) {
            $search = '%' . $request->string('search') . '%';

            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', $search);
            });
        }

        $events = $query->paginate(10)->appends($request->query());

        return Inertia::render('Admin/Events/Index', [
            'events' => $events,
            'filters' => [
                'usage' => $usage,
                'search' => $request->query('search'),
                'sort' => $sort,
                'direction' => $direction,
            ],
            'usageCounts' => $this->usageCounts(),
        ]);
    }

    private function usageCounts(): array
    {
        return [
            'total' => Event::count(),
            'inUse' => Event::whereHas('invitations')->count(),
            'unused' => Event::whereDoesntHave('invitations')->count(),
        ];
    }
}