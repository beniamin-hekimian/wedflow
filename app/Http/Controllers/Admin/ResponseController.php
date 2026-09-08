<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Response;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResponseController extends Controller
{
    private const ATTENDANCE_FILTERS = ['attending', 'declined'];

    private const SORTABLE = [
        'couple_name', 'event_date', 'responses_count', 'created_at',
    ];

    public function index(Request $request)
    {
        $attending = $request->query('attending');
        if (! in_array($attending, self::ATTENDANCE_FILTERS, true)) {
            $attending = null;
        }

        $sort = $request->query('sort', 'created_at');
        if (! in_array($sort, self::SORTABLE, true)) {
            $sort = 'created_at';
        }

        $direction = $request->query('direction', 'asc');
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $search = $request->filled('search')
            ? '%' . $request->string('search') . '%'
            : null;

        $matches = function ($query) use ($attending, $search) {
            if ($attending === 'attending') {
                $query->where('is_attending', true);
            } elseif ($attending === 'declined') {
                $query->where('is_attending', false);
            }

            if ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('guest_name', 'like', $search)
                        ->orWhere('message', 'like', $search);
                });
            }
        };

        $query = Invitation::query()
            ->with(['user:id,name,email'])
            ->with([
                'responses' => function ($builder) use ($matches) {
                    $builder
                        ->where($matches)
                        ->orderBy('created_at')
                        ->orderBy('id');
                },
            ])
            ->withCount([
                'responses as responses_count' => $matches,
                'responses as attending_count' => function ($builder) use ($matches) {
                    $builder->where($matches)->where('is_attending', true);
                },
                'responses as declined_count' => function ($builder) use ($matches) {
                    $builder->where($matches)->where('is_attending', false);
                },
                'responses as wishes_count' => function ($builder) use ($matches) {
                    $builder->where($matches)->whereNotNull('message');
                },
                'responses as guests_count' => function ($builder) use ($matches) {
                    $builder
                        ->where($matches)
                        ->where('is_attending', true)
                        ->selectRaw('coalesce(sum(count), 0)');
                },
            ])
            ->whereHas('responses', $matches);

        switch ($sort) {
            case 'couple_name':
                $query->orderBy('groom_name', $direction)->orderBy('bride_name', $direction);
                break;
            case 'event_date':
                $query->orderBy('event_date', $direction);
                break;
            case 'responses_count':
                $query->orderBy('responses_count', $direction);
                break;
            default:
                $query->orderBy('created_at', $direction);
        }

        $query->orderBy('id', $direction);

        $invitations = $query->paginate(5)->appends($request->query());

        return Inertia::render('Admin/Responses/Index', [
            'invitations' => $invitations,
            'filters' => [
                'attending' => $attending,
                'search' => $request->query('search'),
                'sort' => $sort,
                'direction' => $direction,
            ],
            'attendanceCounts' => $this->attendanceCounts(),
        ]);
    }

    private function attendanceCounts(): array
    {
        return [
            'total' => Response::count(),
            'attending' => Response::where('is_attending', true)->count(),
            'declined' => Response::where('is_attending', false)->count(),
        ];
    }
}