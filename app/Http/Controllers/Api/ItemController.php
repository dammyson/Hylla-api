<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Item\UpdateRequest;
use App\Models\ItemCache;
use App\Models\Product;
use App\Services\Product\CreateService;
use App\Services\Utilities\GetHttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Exceptions\AuthenticationException;

class ItemController extends Controller
{
    
   

    // get all non archived items 
    public function items() {
        try {
            $user = Auth::user();
            
            $items = Product::where('user_id', $user->id)
                    ->with(['stores', 'images', 'productImages'])
                    ->where('archived', false)
                    ->get();
            
            return response()->json($items, 200);
        
        } catch(\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;
    
            if ($throwable instanceof AuthenticationException) {
                $message = "User is unauthenticated";
                $statusCode = 401;
            }
    
            return response()->json([
                'status' => 'failed',
                'message' => $message
            ], $statusCode);
        }
    }

    // get one item
    public function item($item) {
        try {
            $item = Product::with(['stores', 'images'])->find($item);

            if (!$item) {
                throw new ModelNotFoundException('Item does not exist');
            }

            $itemOwnerId  = $item->user_id;
            $user = Auth::user();

            if (!$user) {
                throw new AuthorizationException('not authorized');

            }
            
            if ( $itemOwnerId !== $user->id ) {
                throw new AuthenticationException('not authorized');
                // return response()->json([
                //     'status' => 'failed',
                //     'message' => "not authorized"
                // ]);
            }
    
            return response()->json([
                'status' => 'success',
                'item' => $item
            ]);

        } catch(\Throwable $exception) {
            $message = $exception->getMessage();
            $statusCode = 500;

            if($exception instanceof ModelNotFoundException) {
                $message = 'Item does not exist';
                $statusCode = 404;
            }

            if ($exception instanceof AuthenticationException) {
                $message = 'not authorized';
                $statusCode = 401;
            }

            return response()->json([
                'status'=> 'failed',
                'message' => $message
            ], $statusCode);

        }
       
    }

    // all inventories page
    public function inventories() {

        try {
            $user = Auth::user();

            if(!$user) {
                throw new AuthenticationException();
            }

            $items = Product::where('user_id', $user->id)->with(['stores'])->where('archived', false)->get();
           
         //  return  $items;
            $totalprice = 0;
           
            foreach ($items as $item) {
                // Check if the item has stores and at least one store
                if (isset($item->stores[0])) {
                    $price = $item->stores[0]->price ?? 0; // Use null coalescing to handle empty price
                    $totalprice += $price == "" ? 0 : $price;
                } else {
                    $totalprice += 0; // No store means no price to add
                }
            }
        
            $itemsCount = $items->count(); // or count($items)
            $totalEstimatedValue = $totalprice;// items sum
            $favoriteItemsCount = $items->where('favorite', true)->count(); 
            
            return response()->json([
                'itemsCount'=> $itemsCount,
                'favoriteItemsCount'=> $favoriteItemsCount,
                'totalEstimatedValue' => $totalEstimatedValue
            ], 200);
        
        } catch(Exception $exception) {
            $message = $exception->getMessage();
            $statusCode = 500;
            
            if ($exception instanceof AuthenticationException) {
               $message = 'user is not authenticated';
               $statusCode = 401;
            }

            return response()->json([
                'status' => 'failed',
                'message' => $message

            ], $statusCode);
    
        }
        
    
    }


    // add item
    public function addItem(Request $request){

        $request->validate([
            'code' => 'required',
        ]);

        try {

            $user = Auth::user();
            $cachedDetails = ItemCache::where('code', $request->code)->first();

            if($cachedDetails) {

               
                $product = (new CreateService(json_decode($cachedDetails->details), $request->code,  $user))->run();

                $item = Product::find($product->id);

                return response()->json([
                    "status"=> "succesful",
                    "item" => $item
                ], 200);

            }else{
                return response()->json([
                   "status"=> "Can not found product with that code",
                  ], 500);
            }

           

        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;
            
            if ($throwable instanceof AuthenticationException) {
                $message = 'user is not authenticated';
                $statusCode = 401;
            }
            
            else if ($throwable instanceof ValidationException) {
                $message = 'invalid data type pls fill the input field correctly also make sure to include the category name';
                $statusCode = 422;
            
            } else if ($throwable instanceof ModelNotFoundException) {
                $message = 'Category not found';
                $statusCode = 404;
            }
            return response()->json([
                'status' => "failed",
                'message' => $message
            ], $statusCode);
        }
        
    }


    public function updateItem(UpdateRequest $request, Product $item) {

        $validated = $request->validated();
        try {
        
            $item->fill($validated)->save();

            return response()->json([
                'data'=>  $item,
                'message' =>"Updated"
            ], 200);

        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;

            return response()->json([
                'status'=> 'failed',
                'message' => $message
            ], $statusCode);
        }  

    }


    public function archivedItem() {
        try {
            $user = Auth::user();

            if(!$user) {
                throw new AuthenticationException();
            }

            $archItem = Product::where('user_id', $user->id)
                ->where('archived', true)
                ->with(['stores', 'images'])
                ->get();
    
            return response()->json($archItem, 200);

        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;

            if ($throwable instanceof AuthenticationException) {
               $message = 'user is not authenticated';
               $statusCode = 401;
            }

            response()->json([
                'status' => 'failed',
                'message' => $message
            ], $statusCode);
        }
        
    }


    public function favorite() {
        try {
            $user = Auth::user();
            $favoriteItems = Product::where('user_id', $user->id)
              ->with(['stores', 'images'])
                ->where('favorite', true)->get();
                
            return response()->json($favoriteItems, 200);

        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;

            return response()->json([
                'status' => "failed",
                'message' => $message
            ], $statusCode);
        }
        
    }

    public function estimatedItem(){
        try {
            $items = Product::where('user_id', Auth::user()->id)
                ->where('archived', false)
                ->with(['stores', 'images'])
                ->get();
    
            return response()->json($items, 200);

        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;
            response()->json([
                'status' => 'failed',
                'message' => $message
            ], $statusCode);
        }
    }
}
