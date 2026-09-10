<?php

namespace App\Http\Controllers;

use App\Models\NailStudio;
use Illuminate\Http\Request;

class NailStudioController extends Controller
{
    // Zeigt die Infos des Studios (z. B. für eine "Über uns"-Seite)
    public function show()
    {
        $studio = NailStudio::first();

        return response()->json($studio);
    }

    // Aktualisiert die Studio-Infos (später nur für Admins zugänglich)
    public function update(Request $request)
    {
        $studio = NailStudio::first();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'opening_hours' => 'nullable|string',
        ]);

        $studio->update($validated);

        return response()->json($studio);
    }
}