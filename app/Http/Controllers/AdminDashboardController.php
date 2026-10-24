<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Design;
use App\Models\NailStudio;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Studio des eingeloggten Nutzers, sonst das erste vorhandene
        $studioId = $request->user()->nail_studio_id
            ?? NailStudio::query()->value('id');

        // DATENBANKABFRAGE: Mitarbeiter des Studios
        // inkl. Anzahl ihrer kommenden bestätigten Termine
        $employees = User::query()
            ->where('role', 'employee')
            ->where('nail_studio_id', $studioId)
            ->withCount([
                'assignedAppointments as upcoming_appointments' => fn ($q) =>
                    $q->where('status', 'confirmed'),
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json([
            'stats' => [
                'open_appointments'      => Appointment::where('status', 'requested')->count(),
                'confirmed_appointments' => Appointment::where('status', 'confirmed')->count(),
                'pending_designs'        => Design::where('status', 'pending')->count(),
                'total_revenue'          => Payment::where('status', 'paid')->sum('amount'),
                'employees'              => $employees->count(),
            ],
            'employees' => $employees,
            'recent_appointments' => Appointment::with('user', 'service', 'design')
                ->latest()
                ->take(10)
                ->get(),
            'pending_designs' => Design::where('status', 'pending')
                ->with('user', 'nails.nailShape', 'nails.color')
                ->latest()
                ->take(10)
                ->get(),
        ]);
    }
}