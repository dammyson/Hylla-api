<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    // public function register(): void
    // {
    //     $this->reportable(function (Throwable $e) {
    //         //
    //     });


       
    // }

    // public function report(Throwable $e)
    // {
    //     if($e instanceof NotFoundHttpException) {
    //         Log::info('From report method: '.$e->getMessage());
    //     }

    //     parent::report($e);
    // }

    // public function render($request, Throwable $e)
    // {
    //     if($e instanceof NotFoundHttpException) {
    //         return response()->json([
    //             'message' => 'From render method: Resource not found'
    //         ], Response::HTTP_NOT_FOUND);
    //     }

    //     return parent::render($request, $e);
    // }
}
