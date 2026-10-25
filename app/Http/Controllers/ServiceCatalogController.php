<?php

namespace App\Http\Controllers;

use App\Models\NailStudio;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Öffentliche Leistungsliste für die Startseite.
 * ?nail_studio_id=… wählt das Studio, sonst das erste.
 */
class ServiceCatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $studios = NailStudio::orderBy('id')->get(['id', 'name']);

        $studio = $studios->firstWhere('id', $request->integer('nail_studio_id'))
            ?? $studios->first();

        if (! $studio) {
            return response()->json([
                'studios'   => [],
                'studio_id' => null,
                'services'  => [],
            ]);
        }

        $services = Service::where('nail_studio_id', $studio->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Service $service) => $service->toCatalogArray())
            ->values();

        return response()->json([
            'studios'   => $studios->map(fn (NailStudio $s) => ['id' => $s->id, 'name' => $s->name])->values(),
            'studio_id' => $studio->id,
            'services'  => $services,
        ]);
    }
}
