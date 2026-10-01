<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePaymentController extends Controller
{
    /*  Payment Checkout für Dynammische Daten, mit der Datenbank! 

    public function checkout(Request $request)
    {

        $request->validate([
            'payment_id' => ['required', 'integer', 'exists:payments,id'],
        ]);

        $payment = Payment::findOrFail($request->payment_id);

        if ($payment->status === 'paid') {
            return response()->json([
                'error' => 'Payment already paid.',
            ], 400);
        }

        Stripe::setApiKey(config('cashier.secret'));

        $session = Session::create([
            'mode' => 'payment',

            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $payment->appointment->service->name,
                        ],
                        'unit_amount' => (int) ($payment->appointment->service->price * 100),
                    ],
                    'quantity' => 1,
                ],
            ],

            'metadata' => [
                'payment_id' => $payment->id,
            ],

            'success_url' => url('/payment/success'),
            'cancel_url' => url('/payment/cancel'),
        ]);

        return response()->json([
            'checkout_url' => $session->url,
        ]);
    } */
        // Checkout test für statische Daten, ohne die Datenbank!
        public function checkout(Request $request)
{
    Stripe::setApiKey(config('cashier.secret'));

    $session = Session::create([
        'mode' => 'payment',

        'line_items' => [
            [
                'price_data' => [
                    'currency' => 'eur',

                    'product_data' => [
                        'name' => 'Test Nageldesign',
                    ],

                    'unit_amount' => 1000, // 10,00 €
                ],

                'quantity' => 1,
            ],
        ],

        'success_url' => url('/payment/success'),
        'cancel_url' => url('/payment/cancel'),
    ]);

    return response()->json([
        'checkout_url' => $session->url,
    ]);
}
} 
