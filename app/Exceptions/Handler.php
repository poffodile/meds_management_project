<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException){
                
                return response(view('errors.error_404'), 404);
            }
    
            /*if ($exception instanceof FatalErrorException) {
                //return response()->view('errors.custom', [], 500);
                //return response(view('frontEnd.error_404'), 404);
                //return response(view('frontEnd.dashboard'), 500);
                //return view('frontEnd.dashboard');
            }*/
    
            if ($e instanceof \Symfony\Component\Debug\Exception\FatalErrorException) { //echo 'OK'; die;
            
                $error = $e->getMessage();
                $path = $request->url();
        
                // echo '<pre>'; print_r($e);   die;
    
                return redirect('/bug-report?err='.$error.'&path='.$path);
            }
         
            //return parent::render($request, $e);
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {                                                                                           
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }

    public function render($request, Throwable $exception)
        {
            if ($exception instanceof NotFoundHttpException) {
                return response()->view('errors.error_404', [], 404);
            }

            return parent::render($request, $exception);
        }

    
}
