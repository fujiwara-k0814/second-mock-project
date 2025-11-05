<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;

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

    //guardsで処理分岐(middlewareが機能した際の判別)
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $guard = $exception->guards()[0] ?? null;

        return match ($guard) {
            'admin' => redirect()->guest('/admin/login'),
            'web' => redirect()->guest('/login'),
            default => redirect()->guest('/login'),
        };
    }
}
