<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    "error" => 1,
                    "status_code" => '404',
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                    'data' => []
                ], 404, ['Content-Type => application/json']);
            }
        });
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    "error" => 1,
                    "status_code" => '401',
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                    'data' => []
                ], 401, ['Content-Type => application/json']);
            }
        });
        $this->renderable(function(TokenInvalidException $e, $request){
            return response()->json([
                "error" => 1,
                "status_code" => '401',
                'status' => 'failed',
                'message' => $e->getMessage(),
                'data' => []
            ], 401, ['Content-Type => application/json']);
        });
        $this->renderable(function (TokenExpiredException $e, $request) {
            return response()->json([
                "error" => 1,
                "status_code" => '401',
                'status' => 'failed',
                'message' => $e->getMessage(),
                'data' => []
            ], 401, ['Content-Type => application/json']);
        });

        $this->renderable(function (JWTException $e, $request) {
            return response()->json([
                "error" => 1,
                "status_code" => '401',
                'status' => 'failed',
                'message' => $e->getMessage(),
                'data' => []
            ], 401, ['Content-Type => application/json']);
        });
    }

}
