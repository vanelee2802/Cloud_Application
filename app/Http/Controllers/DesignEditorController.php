<?php

namespace App\Http\Controllers;

use App\Models\NailShape;
use App\Models\Color;
use Inertia\Inertia;
use App\Models\DesignElement;

class DesignEditorController extends Controller
{
    public function index()
{
    $nailShapes = NailShape::all();
    $colors = Color::all();
    $designElements = DesignElement::all();

    return Inertia::render('DesignEditor', [
        'nailShapes' => $nailShapes,
        'colors' => $colors,
        'designElements' => $designElements,
    ]);
}
}