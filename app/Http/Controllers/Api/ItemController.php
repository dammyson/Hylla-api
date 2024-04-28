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

           //$url = 'https://api.upcdatabase.org/product/' . $code;
           $token = 'ar19ee4aamlyfmrebu39auq0a0h8xa';
           $url = 'https://api.barcodelookup.com/v3/products?barcode='. $code.'&key=' . $token;

           $res = new GetHttpRequest($url);
           $res =  $res->run();

          // dd($res);

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


    // get all non archived items 
    public function items() {
        try {
            $user = Auth::user();

            // Incase you want to display all items including the archived ones,
            // then comment the  code directly below and uncomment the following one
    
            // $items = Item::where('user_id', $user->id)->with('category')->where('archived', false)->select(['title', 'subtitle', 'created_at', 'id'])->get();
            // $items = Item::where('user_id', $user->id)->with('category')->where('archived', false)->get();
            
            $items = Item::where('user_id', $user->id)
                    ->with(['category' => function ($query) {
                        $query->select('id', 'name');
                    }])
                    ->where('archived', false)
                    ->get(['title', 'subtitle', 'created_at', 'id', 'category_id']);
            
           
            // $items = Item::where('user_id', $user->id)->select(['title', 'subtitle', 'created_at', 'id'])->get();
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
            $items = Item::where('user_id', $user->id)->where('archived', false);
            // $items = Item::where('user_id', $user->id)

            // IMPORTANT!!!  do not alter the order of the following calculation below
            // as this is a query chain and it could affect what is returned
            $itemsCount = $items->count(); // or count($items)
            $totalEstimatedValue = $items->sum('price');// items sum
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
        try {

            $request->validate([
                'categoryName' => 'required',
                'title' => 'sometimes|required|string',
                'subtitle' => 'sometimes|required|string',
                'description' => 'sometimes|required|string',
                'price' => 'sometimes|required|numeric',
                'product_name' => 'sometimes|required|string',
                'serial_number' => 'sometimes|required|string',
                'product_number' => 'sometimes|required|numeric',
                'lot_number' => 'sometimes|required|numeric',
                'barcode' => 'sometimes|required|numeric',
                'weight' => 'sometimes|required|numeric',
                'dimension' =>'sometimes|required|string',
                'warranty_length' => 'sometimes|required|numeric'
            ]);

            $user = Auth::user();

            //remember to create an error handler if category name does not exist
            $category = Category::where('name', $request->categoryName)->first();
            
            if (!$category) {
                throw new ModelNotFoundException();
            }

            $item = Item::create([
                'category_id' => $category->id,
                'user_id' => $user->id,
                'title' => $request->title ?? '',
                'subtitle' => $request->subtitle ?? '',
                'description' => $request->description ?? '',
                'price' => $request->price ?? 0,
                'product_name' => $request->productName,
                'serial_number' => $request->serialNumber,
                'product_number' => $request->productNumber,
                'lot_number' => $request->lotNumber,
                'barcode' => $request->barcode,
                'weight' => $request->weight,
                'dimension' => $request->dimensions,
                'warranty_length' => $request->warrantyLength,
                'archived' => false
            ]);
            

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
            'title' => 'sometimes|required|string',
            'subtitle' => 'sometimes|required|string',
            'favorite' => 'sometimes|required|boolean',
            'archived' => 'sometimes|required|boolean',
            'product_name' => 'sometimes|required|string',
            'serial_number' => 'sometimes|required|string',
            'product_number' => 'sometimes|required|numeric',
            'lot_number' => 'sometimes|required|numeric',
            'barcode' => 'sometimes|required|numeric',
            'weight' => 'sometimes|required|numeric',
            'dimension' =>'sometimes|required|string',
            'warranty_length' => 'sometimes|required|numeric'
        ]);


        try {
            $itemOwnerId  = $item->user_id;
            $user = Auth::user();

            if ( $itemOwnerId !== $user->id ) {
                throw new AuthorizationException();

                // return response()->json([
                //     'status' => 'failed',
                //     'message' => "not authorized"
                // ]);
            }
        
            $item = $item->update([
                'title' => $request->title ?? $item->title,
                'subtitle' => $request->subtitle ?? $item->subtitle,
                'description' => $request->description ?? $item->description,
                'favorite' => $request->favorite ?? $item->favorite,
                'archived' => $request->archived ?? $item->archived,
                'product_name' => $request->productName ?? $item->product_name,
                'product_number'=> $request->productNumber ?? $item->product_number,
                'serial_number'=> $request->serialNumber ?? $item->serial_number,
                'lot_number' => $request->lotNumber ?? $item->lot_number,
                'barcode' => $request->barcode ?? $item->barcode,
                'weight'=> $request->weight ?? $item->weight,
                'dimension' => $request->dimension ?? $item->dimension,
                'warranty_length' => $request->weight ?? $item->dimension,
            ]);


            return response()->json($item, 200);

        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;

            if ($throwable instanceof AuthorizationException) {
                $message = 'not authorized';
                $statusCode = 401;
            }

            if ($throwable instanceof ValidationException) {
                $message = 'error in form data';
                $statusCode = 422;
            }

            if (strpos($throwable->getMessage(), 'SQLSTATE') !== false) {
                preg_match("/for column '(.+)' at row \d+/", $throwable->getMessage(), $matches);
                $columnName = $matches[1];

                preg_match("/Incorrect (.+) value: '(.+)' for/", $throwable->getMessage(), $matches);
                $enteredValue = $matches[2];
                $wrongType = $matches[1];

                // Get the right type the column accepts (you may need to query the database schema for this)
                $rightType = 'boolean'; // Assuming 'favorite' column accepts boolean values

                // Format the error message
                $errorMessage = "Error: Incorrect value for field '{$columnName}'. ";
                $errorMessage .= "Entered value '{$enteredValue}' is of wrong type '{$wrongType}'. ";
                $errorMessage .= "field '{$columnName}' expects '{$rightType}' type.";

                // Return the formatted error message as JSON
                return response()->json(['status' => 'failed', 'message' => $errorMessage], 422);

                        // return response()->json([
                        //     'status'=> 'failed',
                        //     'message' => $message
                        // ]);


            }

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
                ->select(['title', 'subtitle', 'created_at', 'id'])
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
                ->where('favorite', true)
                ->select(['title', 'subtitle', 'created_at', 'id'])->get();
                
            return response()->json($favoriteItems, 200);

        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;
            
            if ($exception instanceof AuthenticationException) {
                $message = 'user is not authenticated';
                $statusCode = 401;
            
            } else if ( $exception instanceof ValidationException) {
                $message = 'invalid data type pls fill the input field correctly';
                $statusCode = 422;
            }

            return response()->json([
                'status' => "failed",
                'message' => $message
            ], $statusCode);
        }
        
    }

    public function estimatedItem(){
        try {
            $user = Auth::user();

            if (!$user) {
                throw new AuthorizationException('not authorized');

            }

            $items = Item::where('user_id', Auth::user()->id)
                ->select(['id','title', 'description', 'created_at', 'price'])
                ->orderBy('price', 'desc')
                ->get();
    
            return response()->json($items, 200);

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
}
