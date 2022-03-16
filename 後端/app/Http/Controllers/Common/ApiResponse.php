<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Common\ErrorCode;

trait ApiResponse
{
    protected function apiResponse($code = 0, $status = false, $data = [])
    {
        $ErrorCode = new ErrorCode();
        $CodeMessage = $ErrorCode->ErrCodeReference();

        return ([
            'code' => $code,
            'status' => $status ? 'success' : 'error',
            'message' => $CodeMessage[$code],
            'data' => $data,
        ]);
    }
}
