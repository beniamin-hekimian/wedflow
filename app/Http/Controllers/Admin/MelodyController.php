<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Melody;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MelodyController extends Controller
{
    private const USAGE_FILTERS = ['in-use', 'unused'];

    private const SORTABLE = [
        'name', 'file_path', 'invitations_count', 'created_at',
    ];

    public function index(Request $request)
    {
        $usage = $request->query('usage');
        if (! in_array($usage, self::USAGE_FILTERS, true)) {
            $usage = null;
        }

        $sort = $request->query('sort', 'created_at');
        if (! in_array($sort, self::SORTABLE, true)) {
            $sort = 'created_at';
        }

        $direction = $request->query('direction', 'asc');
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Melody::query()->withCount('invitations');

        switch ($sort) {
            case 'name':
                $query->orderBy('name', $direction);
                break;
            case 'file_path':
                $query->orderBy('file_path', $direction);
                break;
            case 'invitations_count':
                $query->orderBy(
                    Invitation::selectRaw('count(*)')->whereColumn('invitations.melody_id', 'melodies.id'),
                    $direction,
                );
                break;
            default:
                $query->orderBy('created_at', $direction);
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
                $builder
                    ->where('name', 'like', $search)
                    ->orWhere('file_path', 'like', $search);
            });
        }

        $melodies = $query->paginate(10)->appends($request->query());

        return Inertia::render('Admin/Melodies/Index', [
            'melodies' => $melodies,
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
            'total' => Melody::count(),
            'inUse' => Invitation::distinct('melody_id')->count('melody_id'),
            'unused' => Melody::whereDoesntHave('invitations')->count(),
        ];
    }
}