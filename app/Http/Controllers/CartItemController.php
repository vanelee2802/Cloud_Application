<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    // Zeigt den Warenkorb des eingeloggten Nutzers
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->cartItems()->with('design')->get()
        );
    }

    // Legt ein Design in den Warenkorb
    public function store(Request $request)
    {
        $validated = $request->validate([
            'design_id' => 'required|exists:designs,id',
            'price' => 'required|numeric|min:0',
        ]);

        $item = $request->user()->cartItems()->create($validated);

        return response()->json($item->load('design'), 201);
    }

    // Entfernt ein Item aus dem Warenkorb
    public function destroy(Request $request, string $id)
    {
        $request->user()->cartItems()->findOrFail($id)->delete();

        return response()->json(['message' => 'Aus Warenkorb entfernt']);
    }
}