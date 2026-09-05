<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total' => Invitation::count(),
                'pending' => Invitation::where('status', 'pending')->count(),
                'active' => Invitation::where('status', 'active')->count(),
            ],
        ]);
    }
}