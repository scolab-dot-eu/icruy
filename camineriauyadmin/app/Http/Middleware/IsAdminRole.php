<?php

namespace App\Http\Middleware;

use Closure;

class IsAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $request->user()->isAdmin()) {
            abort(403, __("No tienes permisos para realizar la operación."));
        }
        return $next($request);
    }
}
