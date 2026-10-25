<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Preisverwaltung: Leistungen anlegen, ändern, löschen (nur Inhaberin, nur eigene Studios).
 */
class ServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $studioId = $request->integer('nail_studio_id');

        $this->authorizeStudio($request, $studioId);

        $services = Service::where('nail_studio_id', $studioId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Service $service) => $service->toCatalogArray())
            ->values();

        return response()->json(['services' => $services]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, withStudio: true);

        $this->authorizeStudio($request, (int) $data['nail_studio_id']);

        $service = Service::create($data);

        return response()->json(['service' => $service->fresh()->toCatalogArray()], 201);
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        $this->authorizeStudio($request, $service->nail_studio_id);

        $service->update($this->validated($request, withStudio: false));

        return response()->json(['service' => $service->fresh()->toCatalogArray()]);
    }

    /**
     * Hat die Leistung schon Termine, wird sie nur ausgeblendet –
     * sonst würden alte Termine und Rechnungen ihre Leistung verlieren.
     */
    public function destroy(Request $request, Service $service): JsonResponse
    {
        $this->authorizeStudio($request, $service->nail_studio_id);

        if ($service->appointments()->exists()) {
            $service->update(['is_active' => false]);

            return response()->json([
                'archived' => true,
                'message'  => 'Diese Leistung hat schon Termine und wurde deshalb nur ausgeblendet.',
                'service'  => $service->fresh()->toCatalogArray(),
            ]);
        }

        $service->delete();

        return response()->json(['archived' => false]);
    }

    private function validated(Request $request, bool $withStudio): array
    {
        $rules = [
            'name'             => ['required', 'string', 'max:100'],
            'category'         => ['required', 'string', 'max:50'],
            'description'      => ['nullable', 'string', 'max:500'],
            'price'            => ['required', 'numeric', 'min:0', 'max:9999'],
            'price_is_from'    => ['boolean'],
            'duration_minutes' => ['required', 'integer', 'min:0', 'max:600'],
            'is_addon'         => ['boolean'],
            'is_popular'       => ['boolean'],
            'is_active'        => ['boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0', 'max:10000'],
        ];

        if ($withStudio) {
            $rules['nail_studio_id'] = ['required', 'integer', 'exists:nail_studios,id'];
        }

        $data = $request->validate($rules);

        $data['price']         = round((float) $data['price'], 2);
        $data['price_is_from'] = $request->boolean('price_is_from');
        $data['is_addon']      = $request->boolean('is_addon');
        $data['is_popular']    = $request->boolean('is_popular');
        $data['is_active']     = $request->has('is_active') ? $request->boolean('is_active') : true;
        $data['sort_order']    = (int) ($data['sort_order'] ?? 0);

        // Eine buchbare Leistung braucht eine Dauer, sonst kann der SlotService keine Zeiten berechnen
        if (! $data['is_addon'] && $data['duration_minutes'] < 5) {
            throw ValidationException::withMessages([
                'duration_minutes' => 'Eine buchbare Leistung braucht mindestens 5 Minuten Dauer.',
            ]);
        }

        return $data;
    }

    private function authorizeStudio(Request $request, int $studioId): void
    {
        $allowed = collect($request->user()->manageableStudioIds())
            ->map(fn ($id) => (int) $id)
            ->contains($studioId);

        abort_unless($allowed, 403);
    }
}
