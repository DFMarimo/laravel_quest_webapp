<?php

namespace App\Traits;

trait ApiResponse
{
    public function successRes($data, $code = 200, $msg = null)
    {
        return response()->json([
            'status' => 'success',
            'message' => $msg,
            'data' => $data,
        ], $code);
    }

    public function errorRes($data = null, $code = 500, $msg = null)
    {
        return response()->json([
            'status' => 'error',
            'message' => $msg,
            'data' => $data,
        ], $code);
    }
}
