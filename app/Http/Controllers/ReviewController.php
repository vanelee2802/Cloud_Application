<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Review;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

/**
 * Bewertungsformular aus der Danke-Mail (signierter Link, kein Login nötig).
 */
class ReviewController extends Controller
{
    public function create(Appointment $appointment)
    {
        abort_unless($appointment->status === 'completed', 404);

        $appointment->load('service', 'nailStudio');

        $alreadyReviewed = Review::where('appointment_id', $appointment->id)->exists();

        return Inertia::render('Reviews/Create', [
            'appointment' => [
                'studio'  => $appointment->nailStudio->name,
                'service' => $appointment->service->name,
                'date'    => Carbon::parse(substr((string) $appointment->getRawOriginal('date'), 0, 10))
                    ->locale('de')
                    ->isoFormat('D. MMMM YYYY'),
            ],
            // Vorschlag: nur der Vorname, damit nicht der volle Name öffentlich wird
            'defaultName'     => strtok((string) $appointment->customer_name, ' ') ?: '',
            'alreadyReviewed' => $alreadyReviewed,
            'submitUrl'       => $alreadyReviewed
                ? null
                : URL::temporarySignedRoute('reviews.store', now()->addHours(2), [
                    'appointment' => $appointment->id,
                ]),
        ]);
    }

    public function store(Request $request, Appointment $appointment)
    {
        abort_unless($appointment->status === 'completed', 404);

        $validated = $request->validate([
            'rating'      => 'required|integer|between:1,5',
            'comment'     => 'nullable|string|max:1000',
            'author_name' => 'required|string|max:60',
        ]);

        // Eine Bewertung pro Termin – der Unique-Index fängt auch gleichzeitige Klicks ab
        if (! Review::where('appointment_id', $appointment->id)->exists()) {
            try {
                Review::create([
                    'appointment_id' => $appointment->id,
                    'nail_studio_id' => $appointment->nail_studio_id,
                    'rating'         => $validated['rating'],
                    'comment'        => $validated['comment'] ?? null,
                    'author_name'    => trim($validated['author_name']),
                ]);
            } catch (UniqueConstraintViolationException) {
                // Wurde gerade parallel gespeichert – nichts zu tun
            }
        }

        // Zurück zum Formular, das jetzt „Danke“ zeigt
        return redirect()->to(URL::temporarySignedRoute('reviews.create', now()->addDay(), [
            'appointment' => $appointment->id,
        ]));
    }
}
