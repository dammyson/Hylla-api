<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Exceptions\AuthenticationException;

class CategoryController extends Controller
{
    // Get categories with Item count
    public function categories() {
        // this is considered the most optimized method from all the methods listed
        // there are severally method below, you can use to just uncomment them as you please
        // 
        try {
            $user = Auth::user();

            if (!$user) {
                throw new AuthorizationException('not authorized');

            }

            $categoriesWithCount = Item::with('category')
                ->select('category_id', \DB::raw('COUNT(*) as item_count'))
                ->where('user_id', auth()->id())
                ->groupBy('category_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'category_name' => $item->category->name ?? null,
                        'item_count' => $item->item_count,
                    ];
                });

            return response()->json($categoriesWithCount);

            // below as some other methods that work but the above is considered the most
            // optimized method
            
                
                // $helperArr = [];
                // $categories = Category::with('items')->get(); // Eager load items relationship

                // $categories->each(function ($category) use (&$helperArr) {
                //     $itemCount = $category->items->filter(function ($item) {
                //         return auth()->id() == $item->user_id;
                //     })->count();

                //     if ($itemCount > 0) {
                //         $helperArr[] = [
                //             "categoryName" => $category->name,
                //             "itemCount" => $itemCount
                //         ];
                //     }
                // });

                // return response()->json($helperArr);

            
            
            // this also works too
            /*
                $helperArr = [];
                $categories = Category::all();

                $itemCount = 0;
                
                // iterate for each category Name
                foreach($categories as $category) {
                    
                    $itemsLength = count($category->items);
                
                    for ($i= 0; $i < $itemsLength; $i++) {
                        
                        // we validate that that has has the right user_id
                            
                        if (auth()->id() == $category->items[$i]->user_id) {
                            //take the Category name
                            $itemCount = $itemCount + 1;
                            if ($i == $itemsLength - 1) {
                                $helperArr[] = ["categoryName" => $category->name, "itemCount" => $itemCount ];
                                $itemCount = 0;
                            }


                        }
                    }
                } 

                return  response()->json($helperArr); 
            

            /*
                //This code works, it is a shorter way to do things 
                //but it the frontend dev will have to iterate over it and sum the items for 
                //each catergory name

            $items = Item::with('category')->where('user_id', auth()->id())->get();


                $data = $items->map(function ($item) {
                    return [
                        'item_id' => $item->id,
                        'item_name' => $item->title, // Assuming item has a 'name' attribute
                        'category_name' => $item->category->name ?? null, 
                    ];
                });

        
                return response()->json($data);

            */
        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;

            if ($throwable instanceof AuthenticationException) {
                $message = 'user is not authenticated';
                $statusCode = 401;
            }

            return response()->json([
                'status'=> 'failed',
                'message' => $message
            ], $statusCode);

        }
   
       
    }


    // add a category
    public function addCategory(Request $request) {
        try {
            $request->validate([
                "name" => 'required',
            ]);

            $category = Category::create(["name"=>$request->name]);
        
            return response()->json([
                "status" => "successfully",
                "category" => $category
            ], 200);

        } catch (\Throwable $throwable) {
            $statusCode = 500;
            
            if ($exception instanceof AuthenticationException) {
                $message = 'user is not authenticated';
                $statusCode = 401;
            }
            
            else if ( $exception instanceof ValidationException) {
                $message = 'invalid data type pls fill the input field correctly';
                $statusCode = 422;
            }
            return response()->json([
                'status' => "failed",
                'message' => $message
            ], $statusCode);
        }
    }
}
