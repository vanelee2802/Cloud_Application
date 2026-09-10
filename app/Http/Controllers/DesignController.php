<?php

namespace App\Http\Controllers;

use App\Models\Design;
use App\Models\DesignElement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DesignController extends Controller
{
    // Zeigt alle Designs des eingeloggten Nutzers
    public function index(Request $request)
    {
        $designs = Design::where('user_id', $request->user()->id)
            ->with('nails.nailShape', 'nails.color', 'nails.designElements')
            ->get();

        return response()->json($designs);
    }

    // Legt ein neues Design inkl. aller Nägel und Elemente an
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nails' => 'required|array|min:1',
            'nails.*.nail_position' => 'required|integer|min:1|max:10',
            'nails.*.nail_shape_id' => 'required|exists:nail_shapes,id',
            'nails.*.color_id' => 'required|exists:colors,id',
            'nails.*.element_ids' => 'array',
            'nails.*.element_ids.*' => 'exists:design_elements,id',
        ]);

        $design = DB::transaction(function () use ($validated, $request) {
            $design = Design::create([
                'user_id' => $request->user()->id,
                'name' => $validated['name'],
                'status' => 'pending',
                'total_price' => 0,
            ]);

            $totalPrice = 0;

            foreach ($validated['nails'] as $nailData) {
                $nail = $design->nails()->create([
                    'nail_position' => $nailData['nail_position'],
                    'nail_shape_id' => $nailData['nail_shape_id'],
                    'color_id' => $nailData['color_id'],
                ]);

                $elementIds = $nailData['element_ids'] ?? [];
                $nail->designElements()->attach($elementIds);

                $totalPrice += DesignElement::whereIn('id', $elementIds)->sum('price_per_nail');
            }

            $design->update(['total_price' => $totalPrice]);

            return $design;
        });

        return response()->json(
            $design->load('nails.nailShape', 'nails.color', 'nails.designElements'),
            201
        );
    }

    // Zeigt ein einzelnes Design mit allen Details
    public function show(string $id)
    {
        $design = Design::with('nails.nailShape', 'nails.color', 'nails.designElements')
            ->findOrFail($id);

        return response()->json($design);
    }

    // Ändert nur den Status (z. B. Mitarbeiter nimmt an/lehnt ab)
    public function update(Request $request, string $id)
    {
        $design = Design::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,accepted,rejected',
            'name' => 'sometimes|string|max:255',
        ]);

        $design->update($validated);

        return response()->json($design);
    }

    public function destroy(string $id)
    {
        Design::findOrFail($id)->delete();

        return response()->json(['message' => 'Design gelöscht']);
    }
}