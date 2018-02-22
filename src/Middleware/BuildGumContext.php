<?php

namespace Code16\Gum\Middleware;

use Closure;
use Code16\Gum\Models\Utils\GumContext;

class BuildGumContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        GumContext::buildFor($request->segments());

        return $next($request);
    }
}