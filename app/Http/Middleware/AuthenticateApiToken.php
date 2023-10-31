<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

final class AuthenticateApiToken
{
   public function handle(Request $request, Closure $next)
   {
        $requestToken = $request->bearerToken();

        if ($requestToken !== Config::get('const.app_api_key')) {
            throw (new AuthorizationException('Access is unauthorized'))->withStatus(401);
        }

        return $next($request);
   }
}
