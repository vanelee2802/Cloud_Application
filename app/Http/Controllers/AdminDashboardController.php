<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Design;
use App\Models\Payment;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'stats' => [
                'open_appointments' => Appointment::where('status', 'requested')->count(),
                'confirmed_appointments' => Appointment::where('status', 'confirmed')->count(),
                'pending_designs' => Design::where('status', 'pending')->count(),
                'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
            ],
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