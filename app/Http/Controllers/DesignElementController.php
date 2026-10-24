<?php

namespace App\Http\Controllers;

use App\Models\DesignElement;
use Illuminate\Http\Request;

class DesignElementController extends Controller
{
       public function index(Request $request)
    {
        $query = DesignElement::query()
            ->with('tags:id,name');

        // Nur aktive Muster mit Bild (fürs DesignPanel): ?patterns=1
        if ($request->boolean('patterns')) {
            $query->patterns()->active();
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Suche in Name UND Tags
        if ($request->filled('search')) {

            $search = '%' . $request->input('search') . '%';

            $query->where(function ($q) use ($search) {
                $q->whereLike('name', $search, caseSensitive: false)
                  ->orWhereHas('tags', fn ($t) =>
                      $t->whereLike('name', $search, caseSensitive: false)
                  );
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_nail', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_nail', '<=', $request->input('max_price'));
        }

        return response()->json(
            $query->orderBy('category')->orderBy('name')->get()
        );
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