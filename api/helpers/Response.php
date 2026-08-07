<?php
/**
 * API Response Helper
 * File: api/helpers/Response.php
 */

class Response
{
    /**
     * Send JSON response
     */
    public static function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Success response
     */
    public static function success($data = null, $message = 'Success')
    {
        self::json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], 200);
    }

    /**
     * Error response
     */
    public static function error($message = 'Error', $statusCode = 400, $errors = null)
    {
        $response = [
            'status' => false,
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        self::json($response, $statusCode);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized($message = 'Unauthorized')
    {
        self::error($message, 401);
    }

    /**
     * Not found response
     */
    public static function notFound($message = 'Not found')
    {
        self::error($message, 404);
    }

    /**
     * Validation error
     */
    public static function validationError($errors)
    {
        self::error('Validation failed', 422, $errors);
    }
}
