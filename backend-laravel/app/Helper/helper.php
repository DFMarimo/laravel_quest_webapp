<?php

use \Illuminate\Http\Response;
use \Illuminate\Support\Str;

function testLoadHelper()
{
    return response()->json('helper is loaded', Response::HTTP_OK);
}

function generateUuid($lengh = 8)
{
    return Str::random($lengh) . '_' . \Carbon\Carbon::now()->microsecond;
}
