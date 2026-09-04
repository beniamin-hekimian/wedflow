<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Inertia\Inertia;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::all();

        return Inertia::render('Templates/Index', [
            'templates' => $templates,
        ]);
    }
}
