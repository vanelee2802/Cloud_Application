<?php

namespace App\Http\Controllers;

use App\Models\NailShape;
use Illuminate\Http\Request;

class NailShapeController extends Controller
{
    public function index()
    {
        return response()->json(NailShape::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        return response()->json(NailShape::create($validated), 201);
    }

    public function show(string $id)
    {
        return response()->json(NailShape::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $shape = NailShape::findOrFail($id);
        $shape->update($request->validate(['name' => 'sometimes|string|max:255']));

        return response()->json($shape);
    }

    public function destroy(string $id)
    {
        NailShape::findOrFail($id)->delete();

        return response()->json(['message' => 'Nagelform gelöscht']);
    }
}