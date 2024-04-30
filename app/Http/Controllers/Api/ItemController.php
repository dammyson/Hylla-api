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
    
   

    // get all non archived items 
    public function items() {
        try {
            $user = Auth::user();
            
            $items = Item::where('user_id', $user->id)
                    ->with(['product', 'product.images'])
                    ->where('archived', false)
                    ->get();
            
           
          
            return response()->json($items, 200);
        
        } catch(\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;
            if ($throwable instanceof AuthenticationException) {
                $message = "user is unauthenticated";
                $statusCode = 401;
            }

            response()->json([
                'status' => 'failed',
                'message' => $message
            ], $statusCode);


        }
       
    }

    // get one item
    public function item($item) {
        try {
            $item = Item::find($item);

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
        
            // Incase you want to display all items including the archived ones,
            // then comment the  code directly below and uncomment the following one
            $items = Item::where('user_id', $user->id)->with(['product', 'product.stores'])->where('archived', false)->get();
            $totalprice = 0;
            foreach($items  as $item){
                $totalprice =  $totalprice  + $item->product->stores[0]->price;
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
            'product_id' => 'required|numeric|exists:products,id',
        ]);


        try {
            $user = Auth::user();

            $item =  Item::create([
                'product_id' => $request->product_id,
                'user_id' =>  $user->id
            ]);

            $item = Item::find($item);

            return response()->json([
                "status"=> "succesful",
                "item" => $item
            ], 200);

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


    public function updateItem(Request $request, Item $item) {

        $request->validate([
            'favorite' => 'sometimes|required|boolean',
            'archived' => 'sometimes|required|boolean',
        ]);

        try {
        
            $item = $item->update([
                'favorite' => $request->favorite ?? $item->favorite,
                'archived' => $request->archived ?? $item->archived,
            ]);

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

            $archItem = Item::where('user_id', $user->id)
                ->where('archived', true)
                ->with(['product', 'product.stores', 'product.images'])
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
            $favoriteItems = Item::where('user_id', $user->id)
              ->with(['product', 'product.stores', 'product.images'])
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
            $items = Item::where('user_id', Auth::user()->id)
                ->with(['product', 'product.stores', 'product.images'])
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
