<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Models\DesignElement;
use App\Models\DesignTemplate;
use App\Models\NailShape;
use Inertia\Inertia;

class DesignEditorController extends Controller
{
    public function index()
    {
        return Inertia::render('DesignEditor', [
            'nailShapes'      => NailShape::all(),
            'colors'          => Color::all(),
            'designElements'  => DesignElement::all(),

            // Vorgefertigte Designs (Vorlagen) mit Tags zum Filtern
            'designTemplates' => DesignTemplate::where('is_active', true)
                ->with('tags:id,name')
                ->orderBy('sort_order')
                ->get(['id', 'name', 'description', 'nails']),
        ]);
    }
}