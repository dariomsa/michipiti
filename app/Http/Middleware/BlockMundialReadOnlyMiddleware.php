<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockMundialReadOnlyMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('mundial_lectura') && $user->getRoleNames()->count() === 1) {
            abort(403, 'Este rol solo puede acceder al Especial Mundial.');
        }

        return $next($request);
    }
}
