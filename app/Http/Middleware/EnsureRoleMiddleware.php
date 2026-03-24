<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $allowedRoles = collect($roles)
            ->flatMap(fn (string $roleGroup): array => explode(',', $roleGroup))
            ->map(fn (string $role): string => trim($role))
            ->filter()
            ->values()
            ->all();

        if (! $user->hasAnyRole($allowedRoles)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
