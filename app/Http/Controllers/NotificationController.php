<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Zeigt alle Benachrichtigungen des eingeloggten Nutzers
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->notifications()->latest()->get()
        );
    }

    // Markiert eine Benachrichtigung als gelesen
    public function update(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->update(['read' => true]);

        return response()->json($notification);
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return response()->json(['message' => 'Benachrichtigung gelöscht']);
    }
}