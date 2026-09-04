<?php

namespace App\Http\Controllers;

use App\Models\Melody;
use Inertia\Inertia;

class MelodyController extends Controller
{
    public function index()
    {
        $melodies = Melody::all();

        return Inertia::render('Melodies/Index', [
            'melodies' => $melodies,
        ]);
    }
}
