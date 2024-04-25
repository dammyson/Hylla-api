<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use App\Services\Utilities\GetHttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Exceptions\AuthenticationException;

class ItemController extends Controller
{
    
    public function scan($code){

        try {

           // $user = Auth::user();

           $url = 'https://api.upcdatabase.org/product/' . $code;
        

           $res = new GetHttpRequest($url);
           $res =  $res->run();

            return response()->json([
                "status"=> "succesful",
                "item" => $res
            ]);

        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            return response()->json([
                'status' => "failed",
                'message' => $message
            ]);
        }
        
    }


}
