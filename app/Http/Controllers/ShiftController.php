<?php

namespace App\Http\Controllers;

use App\Models\EmployeeShift;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Schichten einer Mitarbeiterin festlegen –
 * für Inhaberin (alle eigenen Studios) und Schichtleitung (eigenes Studio).
 */
class ShiftController extends Controller
{
    /**
     * Erwartet:
     * shifts: { monday: { start: "09:00", end: "17:00" }, tuesday: null, … }
     * null bzw. fehlender Tag = frei
     */
    public function update(Request $request, User $employee): RedirectResponse
    {
        // Nur Mitarbeiterinnen und Schichtleitungen aus eigenen Studios
        abort_unless(
            in_array($employee->role, [User::ROLE_EMPLOYEE, User::ROLE_ADMIN], true)
                && in_array($employee->nail_studio_id, $request->user()->manageableStudioIds()),
            403
        );

        $days = array_values(EmployeeShift::WEEKDAYS);

        $request->validate([
            'shifts'           => ['present', 'array'],
            'shifts.*'         => ['nullable', 'array'],
            'shifts.*.start'   => ['required_with:shifts.*', 'date_format:H:i'],
            'shifts.*.end'     => ['required_with:shifts.*', 'date_format:H:i'],
        ], [
            'shifts.*.start.date_format' => 'Bitte gib eine gültige Uhrzeit ein (z. B. 09:00).',
            'shifts.*.end.date_format'   => 'Bitte gib eine gültige Uhrzeit ein (z. B. 17:00).',
        ]);

        $shifts = collect($request->input('shifts', []))
            ->only($days)
            ->filter();

        // Ende muss nach Beginn liegen
        foreach ($shifts as $day => $shift) {
            if ($shift['end'] <= $shift['start']) {
                throw ValidationException::withMessages([
                    "shifts.{$day}.end" => 'Das Schichtende muss nach dem Beginn liegen.',
                ]);
            }
        }

        // Komplette Woche ersetzen
        DB::transaction(function () use ($employee, $shifts) {

            $employee->shifts()->delete();

            foreach ($shifts as $day => $shift) {
                $employee->shifts()->create([
                    'weekday'    => array_search($day, EmployeeShift::WEEKDAYS, true),
                    'start_time' => $shift['start'],
                    'end_time'   => $shift['end'],
                ]);
            }
        });

        return back()->with('success', "Schichten von {$employee->name} gespeichert.");
    }
}
