<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index()
    {
        return response()->json(Color::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hex_code' => 'required|string|max:7',
        ]);

        return response()->json(Color::create($validated), 201);
    }

    public function show(string $id)
    {
        return response()->json(Color::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $color = Color::findOrFail($id);
        $color->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'hex_code' => 'sometimes|string|max:7',
        ]));

        return response()->json($color);
    }

    public function destroy(string $id)
    {
        Color::findOrFail($id)->delete();

        return response()->json(['message' => 'Farbe gelöscht']);
    }
}