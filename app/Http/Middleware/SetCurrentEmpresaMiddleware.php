<?php

namespace App\Http\Middleware;

use App\Support\EmpresaContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentEmpresaMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        app(EmpresaContext::class)->currentId();

        return $next($request);
    }
}
