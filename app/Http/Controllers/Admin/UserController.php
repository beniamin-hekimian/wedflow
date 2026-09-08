<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    private const ROLES = ['admin', 'customer'];

    private const VERIFIED_FILTERS = ['verified', 'unverified'];

    private const SORTABLE = [
        'name', 'email', 'role', 'email_verified_at', 'invitations_count', 'created_at',
    ];

    public function index(Request $request)
    {
        $role = $request->query('role');
        if (! in_array($role, self::ROLES, true)) {
            $role = null;
        }

        $verified = $request->query('verified');
        if (! in_array($verified, self::VERIFIED_FILTERS, true)) {
            $verified = null;
        }

        $sort = $request->query('sort', 'created_at');
        if (! in_array($sort, self::SORTABLE, true)) {
            $sort = 'created_at';
        }

        $direction = $request->query('direction', 'asc');
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = User::query()->withCount('invitations');

        if ($role) {
            $query->where('role', $role);
        }

        if ($verified === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($verified === 'unverified') {
            $query->whereNull('email_verified_at');
        }

        if ($request->filled('search')) {
            $search = '%' . $request->string('search') . '%';

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search);
            });
        }

        $query->orderBy($sort, $direction)->orderBy('id', $direction);

        $users = $query->paginate(10)->appends($request->query());

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'role' => $role,
                'verified' => $verified,
                'search' => $request->query('search'),
                'sort' => $sort,
                'direction' => $direction,
            ],
            'roleCounts' => $this->roleCounts(),
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:' . implode(',', self::ROLES)],
        ]);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $user->update(['role' => $validated['role']]);

        return back()->with(
            'success',
            "{$user->name}'s role updated to " . ucfirst($validated['role']) . '.',
        );
    }

    private function roleCounts(): array
    {
        $counts = User::query()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $result = ['total' => User::count()];

        foreach (self::ROLES as $role) {
            $result[$role] = $counts[$role] ?? 0;
        }

        $result['verified'] = User::whereNotNull('email_verified_at')->count();
        $result['unverified'] = User::whereNull('email_verified_at')->count();

        return $result;
    }
}