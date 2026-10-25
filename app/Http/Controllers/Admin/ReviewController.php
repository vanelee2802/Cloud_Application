<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Inhaberin: Bewertungen ihrer Studios sehen und unpassende ausblenden.
 */
class ReviewController extends Controller
{
    // Seite /admin/bewertungen
    public function page(Request $request)
    {
        return Inertia::render('Admin/Reviews', [
            'studios' => $request->user()
                ->ownedStudios()
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    // Liste für die Seite, auch ausgeblendete
    public function index(Request $request): JsonResponse
    {
        $studioIds = $this->studioIds($request);

        if ($request->filled('nail_studio_id')) {
            abort_unless(in_array($request->integer('nail_studio_id'), $studioIds, true), 403);

            $studioIds = [$request->integer('nail_studio_id')];
        }

        $reviews = Review::with('nailStudio:id,name', 'appointment.service:id,name')
            ->whereIn('nail_studio_id', $studioIds)
            ->latest('id')
            ->limit(200)
            ->get()
            ->map(fn (Review $review) => [
                'id'          => $review->id,
                'rating'      => $review->rating,
                'comment'     => $review->comment,
                'author_name' => $review->author_name,
                'is_hidden'   => $review->is_hidden,
                'studio'      => $review->nailStudio?->name,
                'service'     => $review->appointment?->service?->name,
                'date'        => $review->created_at?->locale('de')->isoFormat('D. MMM YYYY'),
            ])
            ->values();

        return response()->json(['reviews' => $reviews]);
    }

    // Ausblenden oder wieder anzeigen
    public function update(Request $request, Review $review): JsonResponse
    {
        abort_unless(in_array((int) $review->nail_studio_id, $this->studioIds($request), true), 403);

        $validated = $request->validate([
            'is_hidden' => 'required|boolean',
        ]);

        $review->update(['is_hidden' => $validated['is_hidden']]);

        return response()->json([
            'id'        => $review->id,
            'is_hidden' => $review->is_hidden,
        ]);
    }

    private function studioIds(Request $request): array
    {
        return collect($request->user()->manageableStudioIds())
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
