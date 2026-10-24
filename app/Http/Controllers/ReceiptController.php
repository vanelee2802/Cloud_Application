<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\User;
use App\Services\ReceiptPdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class ReceiptController extends Controller
{
    /**
     * Kundin: eigene Rechnungen.
     * Auch Rechnungen aus Gast-Buchungen mit derselben E-Mail.
     */
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();

        $receipts = Receipt::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('customer_email', $user->email);
            })
            ->latest('issued_at')
            ->get(['id', 'number', 'studio_name', 'gross_cents', 'service_date', 'issued_at']);

        return Inertia::render('Rechnungen/Index', [
            'receipts' => $receipts,
            'mode'     => 'customer',
        ]);
    }

    /**
     * Studio: alle Rechnungen der Studios, die die Person verwalten darf.
     * Inhaberin: alle eigenen Studios. Schichtleitung: eigenes Studio.
     */
    public function studioIndex(Request $request): InertiaResponse
    {
        $studioIds = $request->user()->manageableStudioIds();

        $receipts = Receipt::query()
            ->whereIn('nail_studio_id', $studioIds)
            ->latest('issued_at')
            ->get([
                'id', 'number', 'studio_name', 'customer_name',
                'net_cents', 'vat_cents', 'gross_cents',
                'service_date', 'issued_at',
            ]);

        return Inertia::render('Rechnungen/Index', [
            'receipts' => $receipts,
            'mode'     => 'studio',
        ]);
    }

    /** PDF herunterladen – nur wer die Rechnung sehen darf */
    public function download(Request $request, Receipt $receipt, ReceiptPdf $pdf): Response
    {
        abort_unless($this->canView($request->user(), $receipt), 403);

        return response($pdf->render($receipt), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $pdf->filename($receipt) . '"',
        ]);
    }

    /**
     * Darf diese Person die Rechnung sehen?
     * - die Kundin selbst (Konto oder gleiche E-Mail)
     * - Inhaberin oder Schichtleitung des Studios
     */
    private function canView(User $user, Receipt $receipt): bool
    {
        if ($receipt->user_id === $user->id || $receipt->customer_email === $user->email) {
            return true;
        }

        return in_array($receipt->nail_studio_id, $user->manageableStudioIds());
    }
}