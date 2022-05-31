<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthorizationException) {

            return response()->json(['status' => 'error',
                'message' => $exception->getMessage(),
            ], 403);
        }
        if ($exception instanceof UnauthorizedHttpException) {
            if ($request->is('api/*')) {
                return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 401);
            }
        }
        if ($exception instanceof RouteNotFoundException) {
            if ($request->is('api/*')) {
                return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 404);
            }
        }
        if ($exception instanceof ModelNotFoundException) {
            if ($request->is('api/*')) {
                return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 404);
            }
        }
        if ($exception instanceof AuthenticationException) {
            // return response()->json(['status'=>'error','message'=>'Kindly login to continue']);
            if ($request->is('api/*')) {
                return response()->json(['status' => 'error', 'message' => 'Kindly login to continue'], 401);
            }
        }
        return parent::render($request, $exception);
    }
}
