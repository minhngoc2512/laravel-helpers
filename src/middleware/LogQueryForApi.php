<?php

namespace Ngocnm\LaravelHelpers\middleware;

use Closure;
use Illuminate\Http\Request;

class LogQueryForApi
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
        $response = $next($request);
        if(config('helper.log_query')==true){
            $data = json_decode($response->getContent());
            if(empty($data)) return $response;
            $query_log = \DB::getQueryLog();
            if (is_array($data)) {
                $data['query_log'] = $query_log;
            } else if (is_object($data)) {
                $data->query_log = $query_log;
            }
            $response->setContent(json_encode($data));
        }
        return $response;
    }
}
