<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Product;
use App\Services\Product\CreateService;
use App\Services\Utilities\GetHttpRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class ProductController extends Controller
{
    
    public function scan($code){


        $user = Auth::user();

        try {

            $product = Product::where('code', $code)->first();


            if (!$product) {
                Log::info("Cannot find in the Db cheeck api");
                $url = 'https://api.barcodelookup.com/v3/products?barcode='. $code.'&key=ar19ee4aamlyfmrebu39auq0a0h8xa';

                $res = new GetHttpRequest($url);
                $res =  $res->run();
     
               $product = (new CreateService($res->products[0], $code))->run();
               
            }

            $product = Product::where('code', $code) ->with(['images'])->first();
          
            return response()->json([
                "status"=> "succesful",
                "item" => $product
            ]);
          


        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            return response()->json([
                'status' => "failed",
                'message' =>  $message
            ],500);
        }
        
    }
}
