<?php

namespace App\Helpers;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ResponseApiHelper
{
    // This is reponse status when status is success
    public static function success(
        string $message,
        array|object|null $data = null,
        int $statusCode = 200,
        string $status = 'Success'
    )
    {
        // check data is a resource collection
        if ($data instanceof ResourceCollection) {
            $dataArray = $data->response()->getData(true);
            return response()->json([
                'status'  => $status,
                'message' => $message,
                'data'    => $dataArray['data'],
                'meta'    => $dataArray['meta'] ?? null,
                'links'   => $dataArray['links'] ?? null,
            ], $statusCode);
        }
        
        return response()->json([
            'status'    => $status,
            'message'   => $message,
            'data'      => $data
        ], $statusCode);
    }

    // This is reponse status when status is error
    public static function error(
        string $message,
        array|object|null $data = null,
        int $statusCode = 500,
        string $status = 'Errors'
    )
    {
        return response()->json([
            'status'    => $status,
            'message'   => $message,
            'data'      => $data
        ], $statusCode);
    }
}