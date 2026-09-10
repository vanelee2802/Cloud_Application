<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    // Zeigt Termine: Kunden sehen ihre eigenen, Mitarbeiter/Admins sehen alle
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Appointment::with('service', 'nailStudio', 'design', 'employee', 'user');

        if ($user->role === 'customer') {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->get());
    }

    // Kunde bucht einen neuen Termin
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nail_studio_id' => 'required|exists:nail_studios,id',
            'service_id' => 'required|exists:services,id',
            'design_id' => 'nullable|exists:designs,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
        ]);

        $appointment = Appointment::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'status' => 'requested',
        ]);

        return response()->json($appointment->load('service', 'nailStudio'), 201);
    }

    // Zeigt einen einzelnen Termin
    public function show(string $id)
    {
        $appointment = Appointment::with('service', 'nailStudio', 'design', 'employee', 'user', 'payment')
            ->findOrFail($id);

        return response()->json($appointment);
    }

    // Mitarbeiter bestätigt/lehnt ab, oder weist sich selbst zu
    public function update(Request $request, string $id)
    {
        $appointment = Appointment::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:requested,confirmed,rejected,completed',
            'employee_id' => 'sometimes|exists:users,id',
        ]);

        $appointment->update($validated);

        return response()->json($appointment->load('service', 'nailStudio', 'employee'));
    }

    public function destroy(string $id)
    {
        Appointment::findOrFail($id)->delete();

        return response()->json(['message' => 'Termin storniert']);
    }
}