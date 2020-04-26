<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponser;

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
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // return parent::render($request, $exception);
        $response = $this->handleException($request, $exception);
        return $response;
    }

    // la siguiente función es para poder ejecutar las cabeceras antes de los mensajes de error y retornar bien los errores
    public function handleException($request, Throwable $exception) {
        if($exception instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        if($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof ModelNotFoundException) {
            $modelName = class_basename($exception->getModel());
            return $this->errorResponse("Does not exists any instance of {$modelName} the specified id.", 404);
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->errorResponse('Does not exists any endpoint matching with that URL.', 404);
        }

        if ($exception instanceof MethodNotAllowedException) {
            return $this->errorResponse('HTTP method does not match with any endpoint.', $exception->getStatusCode());
        }

        if ($exception instanceof HttpException) {
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }

        if (config('app.debug')) {
            return parent::render($request, $exception);
        }

        return $this->errorResponse('Unexpected error', 500);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if( $this->isFrontEnd( $request ) ) {
            return redirect()->guest($exception->redirectTo() ?? route('login'));
        }

        return $this->errorResponse('Unauthenticated', 401);
    }

    protected function convertValidationExceptionToResponse(ValidationException $exception, $request)
    {
        $errors = $exception->validator->errors()->getMessages();

        if( $this->isFrontEnd($request) ) {
            return $request->ajax() ? response()->json( $errors, 422) : redirect()->back()->withInput($request->input())->withErrors($errors);
        }


        return $this->errorResponse($errors, 422);
    }

    public function isFrontEnd($request) {
        return $request->acceptsHtml() && collect($request->route()->middleware())->contains('web');
    }
}
