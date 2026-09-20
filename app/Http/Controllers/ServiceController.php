<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // Zeigt alle Dienstleistungen
    public function index()
    {
        $services = Service::all();

        return response()->json($services);
    }

    // Legt eine neue Dienstleistung an
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nail_studio_id' => 'required|exists:nail_studios,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        $service = Service::create($validated);

        return response()->json($service, 201);
    }

    // Zeigt eine einzelne Dienstleistung
    public function show(string $id)
    {
        $service = Service::findOrFail($id);

        return response()->json($service);
    }

    // Aktualisiert eine Dienstleistung
    public function update(Request $request, string $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'duration_minutes' => 'sometimes|integer|min:1',
        ]);

        $service->update($validated);

        return response()->json($service);
    }

    // Löscht eine Dienstleistung
    public function destroy(string $id)
    {
        Service::findOrFail($id)->delete();

        return response()->json(['message' => 'Dienstleistung gelöscht']);
    }
}