<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

// Success response
if (!function_exists('success_response')) {
    function success_response($message = 'Data found!', $data = null, $status_code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status_code);
    }
}

// Error response
if (!function_exists('error_response')) {
    function error_response($message = 'Error!', $data = null, $status_code = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $status_code);
    }
}
