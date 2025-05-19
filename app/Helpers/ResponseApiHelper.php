<?php

namespace App\Helpers;

class ResponseApiHelper
{
    // This is reponse status when status is success
    public static function success(
        string $message,
        array|object $data = [],
        int $statusCode = 200,
        string $status = 'Success'
    )
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    // This is reponse status when status is error
    public static function error(
        string $message,
        array|object $data = [],
        int $statusCode = 500,
        string $status = 'Errors'
    )
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
}