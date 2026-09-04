<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvitationController extends Controller
{
    public function create(Request $request)
    {
        $template = Template::findOrFail($request->query('template'));

        return Inertia::render('Invitations/Create', [
            'template' => $template,
        ]);
    }
}
