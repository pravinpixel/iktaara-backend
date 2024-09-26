<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtAuthenticate extends BaseMiddleware
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
        $user = $this->auth->parseToken()->authenticate();

        if(isset($request->customer_id)){
            if((isset($user)) && $request->customer_id != auth()->guard('api')->user()->id){
                return response()->json(array('error' => 1, 'status_code' => 400, 'message' => 'You are not authorised to see this data', 'status' => 'failed', 'data' => []), 401);
            }
        }

        return $next($request);
    }
}
