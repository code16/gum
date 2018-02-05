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
        $segments = $request->segments();
        array_splice($segments, 0, 1);

        GumContext::buildFor($segments);

        return $next($request);
    }
}