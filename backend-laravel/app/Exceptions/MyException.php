<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class MyException extends Exception
{
    public function report()
    {
        return Log::info('error');
    }

    public function render($request)
    {
        dd($request->all());
    }
}
