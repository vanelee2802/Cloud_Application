<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Prüft, ob der eingeloggte Nutzer eine der erlaubten Rollen hat.
     * Nutzung in Routen: ->middleware('role:employee,admin')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            abort(403, 'Du hast keine Berechtigung für diese Aktion.');
        }

        return $next($request);
    }
}