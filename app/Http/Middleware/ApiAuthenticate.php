<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiAuthenticate
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

        if( !$request->customer_id ) {
            $response_message = array( 'error' => 1, 'status_code' => 401, 'status' => 'error', 'message' => 'Customer Token required.', 'status' => 'failed', 'data' => []);
            return response()->json($response_message);
        }

        // if($request->customer_id != auth()->guard('api')->user()->id){
        //     return response()->json(array('error' => 1, 'status_code' => 401, 'message' => 'You are not authorised to see this data', 'status' => 'failed', 'data' => []), 401);
        // }
        return $next($request);
    }
}
