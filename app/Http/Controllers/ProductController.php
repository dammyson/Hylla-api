<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemCache;
use App\Models\Product;
use App\Models\ProductImages;
use App\Services\Product\CreateService;
use App\Services\Utilities\GetHttpRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class ProductController extends Controller
{
    
    public function scan($code){


        $user = Auth::user();

        try {

            $cachedDetails = ItemCache::where('code', $code)->first();
            if($cachedDetails) {

                $object = [
                    'id' => $cachedDetails->id,
                    'code' => $cachedDetails->code,
                    'details' => json_decode($cachedDetails->details)
                ];

                return response()->json([
                    "status"=> "succesful",
                    "item" => $object
                ]);


            }else{
                Log::info("Cannot find in the Db cheeck api");
                $url = 'https://api.barcodelookup.com/v3/products?barcode='. $code.'&key=ar19ee4aamlyfmrebu39auq0a0h8xa';
                
                $res = new GetHttpRequest($url);
                $res =  $res->run();

            
                $productDetailsJson = json_encode($res->products[0]);

                $cachedDetails = ItemCache::create([
                    'code' => $code,
                    'details' => $productDetailsJson
                ]);
                
                $object = [
                    'id' => $cachedDetails->id,
                    'code' => $cachedDetails->code,
                    'details' => json_decode($cachedDetails->details)
                ];

                return response()->json([
                    "status"=> "succesful",
                    "item" => $object
                ]);
        
            }
          
           
          


        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            return response()->json([
                'status' => "failed",
                'message' =>  $message
            ],500);
        }
        
    }




    public function loadImages(){

        $url = 'https://apis.supplierxm.salsify.com/public/v1/products';
        $res = new GetHttpRequest($url, true);
        $res =  $res->run();

        foreach ($res->data as $product) {
            if (!empty($product->assets->pictures)) {
                // Check if a record with the same barcode already exists
                $productImage = ProductImages::where('barcode', $product->gtin)->first();
                $urls = collect($product->assets->pictures)->pluck('url')->toArray();
                if ($productImage) {
                    // Update the existing record
                  
                    $productImage->image_path =  $urls;
                    $productImage->save();
                } else {
                    // Create a new record if it doesn't exist
                    $productImage = new ProductImages();
                    $productImage->barcode = $product->gtin;
                    $productImage->image_path =  $urls;
                    $productImage->save();
                }
            }
        }
        return response()->json([
            "status"=> "succesful",
            "Message" => "Images updated"
        ]);
    }

    public function getCacheItem() {
      return  ItemCache::get();
    }

}
