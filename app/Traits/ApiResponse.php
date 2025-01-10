<?php

namespace App\Traits;

use App\Enums\{ResponseStatusCode, ResponseErrorCode};
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Send a response with JSON data, status code, message, and error code.
     *
     * @param mixed $data task result
     * @param int $statusCode response status code
     * @param string $message description of the response
     * @param string $errorCode response error code
     * @return JsonResponse
     */
    public function sendResponse($data = null, $statusCode = ResponseStatusCode::OK, $message = null, $errorCode = null): JsonResponse
    {
        $response = [];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        if (!is_null($message)) {
            $response['message'] = $message;
        }

        if (!is_null($errorCode)) {
            $response['errorCode'] = $errorCode instanceof ResponseErrorCode ? $errorCode->value : $errorCode;
        }

        return response()->json($response, $statusCode->value);
    }
}
