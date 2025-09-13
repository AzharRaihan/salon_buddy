<?php
namespace App\Traits;

trait ApiResponse
{
    public function successResponse($data, $message = 'Information has been saved successfully!', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public function errorResponse($message = 'Something went wrong!', $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    public function notFoundResponse($message = 'Information not found!', $code = 404)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    public function validationErrorResponse($errors, $message = 'Validation Error', $code = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }
}
