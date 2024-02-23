<?php

namespace Ngocnm\LaravelHelpers\exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Request;
use Ngocnm\LaravelHelpers\jobs\SendMessageToSlack;
use Throwable;

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
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            $ip = config('app.ip_server', 'localhost');
            $message = "- Source: " . config('app.name', 'localhost') . ": " . $ip;
            $message .= "\n- Path: " . url()->full();
            $message .= "\n- Method: " . Request::method();
            $message .= "\n- Client IP: " . Request::ip();
            $message .= "\n- Error: " . $e->getMessage();
            $message .= "\n- Date: " . date('H:i:s d/m/Y');
            $message .= "\n`" . $e->getFile() . "(" . $e->getLine() . ")`\n";
            $message .= "```" . json_encode(data_get($e->getTrace(), '0', null)) . "```";
            dispatch(new SendMessageToSlack($message, 'error'));
        });
    }
}