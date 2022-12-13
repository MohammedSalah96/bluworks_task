<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class CheckPermission {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $page, $permission = "open") {

        if (!\Permissions::check($page, $permission)) {
            $message = _lang('app.access_denied');
            return _api_json('',['message' => $message], 403);
        }
        return $next($request);
    }

}
