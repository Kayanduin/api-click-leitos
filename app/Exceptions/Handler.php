<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
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
    }

    public function render($request, Throwable $exception): JsonResponse
    {
        if ($this->isHttpException($exception)) {
            switch ($exception->getStatusCode()) {
                case 404:
                    return response()->json(
                        ['errors' => 'Invalid route.'],
                        404
                    );
                case 500:
                    return response()->json(
                        ['errors' => 'Internal error.'],
                        500
                    );
            }
        }
        if ($exception instanceof RouteNotFoundException) {
            return response()->json(
                ['errors' => 'Authentication error.'],
                401
            );
        }
        return response()->json(
            ['errors' => 'Internal error.'],
            500
        );
    }
}
