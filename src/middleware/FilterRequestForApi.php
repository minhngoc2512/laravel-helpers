<?php

namespace Ngocnm\LaravelHelpers\middleware;
use Closure;
use Illuminate\Http\Request;
use Ngocnm\LaravelHelpers\Helper;

class FilterRequestForApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        Helper::BaseApiRequest()->filterRequest();
        return $next($request);
    }

}