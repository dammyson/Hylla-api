<?php

namespace App\Http\Controllers;

use Google\Service\CloudSearch\ErrorMessage;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    /**
     * success response method
     * @return \Illuminate\Http\Response
    */
    public function sendResponse($result, $message, $code = 200)
    {
        $response = [
            "success" => true,
            "data" => $result,
            "message" => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * return error response
     * 
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages= [], $code = 404)
    {
        $response = [
            "success" => true,
            "message" => $error,
        ];

        if (!empty($errorMessage)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
