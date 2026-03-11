<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;


class BaseApiController extends Controller
{
    protected function sendResponse($data = null, $message = 'Success', $statusCode = 200)
    {

        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data) {
            $response['data'] = $data;
        }
        return response()->json($response, $statusCode);
    }

    protected function errorResponse($message = 'Error', $statusCode = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        return response()->json($response, $statusCode);
    }
    public function sendValidationError($validator)
    {
        return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
    }
}
