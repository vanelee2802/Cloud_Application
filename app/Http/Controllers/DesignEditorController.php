<?php

namespace App\Http\Controllers;

use App\Models\NailShape;
use App\Models\Color;
use Inertia\Inertia;

class DesignEditorController extends Controller
{
    public function index()
    {
        $nailShapes = NailShape::all();
        $colors = Color::all();

        return Inertia::render('DesignEditor', [
            'nailShapes' => $nailShapes,
            'colors' => $colors,
        ]);
    }
}