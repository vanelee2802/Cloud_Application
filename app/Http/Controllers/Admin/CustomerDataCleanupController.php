<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CustomerDataCleanupService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerDataCleanupController extends Controller
{
    public function __construct(private CustomerDataCleanupService $service)
    {
    }

    public function preview(Request $request)
    {
        $studioIds = $this->studioIds($request);

        $data = $request->validate([
            'mode' => ['required', Rule::in(['anonymize', 'delete'])],
        ]);

        return response()->json($this->service->preview($data['mode'], $studioIds));
    }

    public function run(Request $request)
    {
        $studioIds = $this->studioIds($request);

        $data = $request->validate([
            'mode' => ['required', Rule::in(['anonymize', 'delete'])],
            'confirmation' => ['required', Rule::in(['LÖSCHEN'])],
            'password' => ['required', 'current_password'],
        ], [
            'confirmation.in' => 'Bitte gib zur Bestätigung genau LÖSCHEN ein.',
            'password.current_password' => 'Das Passwort ist nicht korrekt.',
        ]);

        return response()->json($this->service->run($data['mode'], $studioIds));
    }

    /**
     * Nur Owner und Admins mit eigenem Studio dürfen bereinigen.
     */
    private function studioIds(Request $request): array
    {
        $ids = $request->user()?->manageableStudioIds() ?? [];
        abort_if(empty($ids), 403);

        return $ids;
    }
}