<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;

/**
 * Studioleitung: Anfragen für Termine am selben Tag bearbeiten
 */
class AppointmentRequestController extends Controller
{
    // Alle offenen Anfragen, früheste zuerst
    public function index()
    {
        $requests = Appointment::with('service')
            ->where('status', 'requested')
            ->orderBy('date')
            ->orderBy('time')
            ->get([
                'id', 'service_id', 'date', 'time',
                'customer_name', 'customer_email', 'created_at',
            ]);

        return response()->json($requests);
    }
}