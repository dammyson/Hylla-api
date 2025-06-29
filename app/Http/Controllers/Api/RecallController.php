<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\Recall;
use Illuminate\Support\Facades\Auth;

use Laravel\Passport\Exceptions\AuthenticationException;

class RecallController extends Controller
{
    
    // get all non archived items 
    public function recallItems() {
        try {
            
            $items = Recall::get();
            
            return response()->json($items, 200);
        
        } catch(\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;
            if ($throwable instanceof AuthenticationException) {
                $message = 'Unauthenticated';
                $statusCode = 401;
            } elseif ($throwable instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $message = 'No items found';
                $statusCode = 404;
            } elseif ($throwable instanceof \Illuminate\Validation\ValidationException) {
                $message = 'Validation error';
                $statusCode = 422;
            }
            return response()->json([
                'status' => 'failed',
                'message' => $message
            ], $statusCode);
        }
    } 
}
