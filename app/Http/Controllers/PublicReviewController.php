<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\AppointmentCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Öffentliche Bewertungen für die Startseite: Durchschnitt, Anzahl, neueste Bewertungen.
 * Ausgeblendete Bewertungen zählen nirgends mit.
 * ?nail_studio_id=… zeigt nur ein Studio, ohne Angabe alle Studios.
 */
class PublicReviewController extends Controller
{
    public function index(Request $request, AppointmentCompletion $completion): JsonResponse
    {
        // Vorbei gegangene Termine abschließen – höchstens alle 10 Minuten, klappt auch ohne Cron
        $completion->runIfDue();

        $limit = min(max($request->integer('limit', 6), 1), 24);

        $query = Review::where('is_hidden', false)
            ->when(
                $request->filled('nail_studio_id'),
                fn ($q) => $q->where('nail_studio_id', $request->integer('nail_studio_id'))
            );

        $count   = (clone $query)->count();
        $average = $count > 0 ? round((float) (clone $query)->avg('rating'), 1) : null;

        $reviews = (clone $query)
            ->with('nailStudio:id,name')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (Review $review) => [
                'id'          => $review->id,
                'rating'      => $review->rating,
                'comment'     => $review->comment,
                'author_name' => $review->author_name,
                'studio'      => $review->nailStudio?->name,
                'date'        => $review->created_at?->locale('de')->isoFormat('MMMM YYYY'),
            ])
            ->values();

        return response()->json([
            'average' => $average,
            'count'   => $count,
            'reviews' => $reviews,
        ]);
    }
}
