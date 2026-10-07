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

                $totalPrice += DesignElement::whereIn('id', $elementIds)
                    ->sum('price_per_nail');
            }

            $design->update([
                'total_price' => $totalPrice,
            ]);

            return $design;
        });

           return redirect()->back()->with([
                'success' => 'Design wurde erfolgreich gespeichert.',
                'total_price' => $design->total_price,
                'design_id' => $design->id,
        ]);
    }

    // Zeigt ein einzelnes Design mit allen Details
    public function show(Request $request, string $id)
    {
        $design = Design::with(
            'nails.nailShape',
            'nails.color',
            'nails.designElements'
        )
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($design);
    }

    // Ändert den Status bzw. Namen eines Designs
    public function update(Request $request, string $id)
    {
        $user = $request->user();

        // Mitarbeiter und Admins dürfen auch Designs von Kunden bearbeiten.
        // Kunden dürfen nur ihre eigenen Designs bearbeiten.
        if ($user->hasAnyRole(['employee', 'admin'])) {
            $design = Design::findOrFail($id);
        } else {
            $design = Design::where('user_id', $user->id)
                ->findOrFail($id);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,accepted,rejected',
            'name' => 'sometimes|string|max:255',
        ]);

        $oldStatus = $design->status;

        $design->update($validated);

        // Bei einer Statusänderung wird der Kunde benachrichtigt.
        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            $messages = [
                'accepted' => 'Dein Design wurde angenommen.',
                'rejected' => 'Dein Design wurde leider abgelehnt.',
            ];

            if (isset($messages[$validated['status']])) {
                $design->user->notifications()->create([
                    'type' => 'design_status_changed',
                    'message' => $messages[$validated['status']],
                    'read' => false,
                ]);
            }
        }

        return response()->json($design);
    }

    // Löscht nur ein eigenes Design
    public function destroy(Request $request, string $id)
    {
        $design = Design::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $design->delete();

        return response()->json([
            'message' => 'Design gelöscht',
        ]);
    }
}