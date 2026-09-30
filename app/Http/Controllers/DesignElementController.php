<?php

namespace App\Http\Controllers;

use App\Models\DesignElement;
use Illuminate\Http\Request;

class DesignElementController extends Controller
{
    public function index(Request $request)
{
    $query = DesignElement::query();

    if ($request->filled('category')) {
        $query->where('category', $request->input('category'));
    }

    if ($request->filled('search')) {
        $query->where('name', 'like', '%' . $request->input('search') . '%');
    }

    if ($request->filled('min_price')) {
        $query->where('price_per_nail', '>=', $request->input('min_price'));
    }

    if ($request->filled('max_price')) {
        $query->where('price_per_nail', '<=', $request->input('max_price'));
    }

    return response()->json($query->get());
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'price_per_nail' => 'required|numeric|min:0',
        ]);

        return response()->json(DesignElement::create($validated), 201);
    }

    public function show(string $id)
    {
        return response()->json(DesignElement::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $element = DesignElement::findOrFail($id);
        $element->update($request->validate([
            'category' => 'sometimes|string|max:255',
            'name' => 'sometimes|string|max:255',
            'price_per_nail' => 'sometimes|numeric|min:0',
        ]));

        return response()->json($element);
    }

    public function destroy(string $id)
    {
        DesignElement::findOrFail($id)->delete();

        return response()->json(['message' => 'Design-Element gelöscht']);
    }
}