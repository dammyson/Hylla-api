<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FilterController extends Controller
{
    //
    public function orderController(Request $request) {
        $request->validate([
            'order_by' => 'required|string', // Assuming the form field is named 'order_by'
        
        ]);

        // Get the authenticated user
        $user = Auth::user();
        
        if ($request->order_by) {
            $orderBy = $request->order_by;
            // Query categories associated with items belonging to the authenticated user
            $categories = $user->items()->with('category')->get()->pluck('category')->unique();

            // Apply ordering

            if ($orderBy == 'asc') {
                $categories = $categories->sortBy('created_at');

            } else if ( $orderBy == 'desc') {
                $categories = $categories->sortByDesc('created_at');
            
            } else if ($orderBy == 'name' ) {
                $categories = $categories->sortBy('name');
            }

            // Get item count for each category
            $categoryData = $categories->map(function ($category) use ($user) {
                $itemCount = $user->items()->where('category_id', $category->id)->count();
                return [
                    'category' => $category,
                    'item_count' => $itemCount
                ];
            });

            return response()->json($categoryData);
        };
    }

    public function filterController(Request $request)
    {
       // Validate the request data
        $request->validate([
            'category_name' => 'required|string', // Assuming the form field is named 'category_name'
        
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Get the category name from the request
        $categoryName = $request->input('category_name');

        // Find the category by name
        $category = Category::where('name', $categoryName)->first();

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        // Count the number of items in the category for the authenticated user
        $itemCount = $user->items()->where('category_id', $category->id)->count();

        return response()->json([
            'category' => $category,
            'item_count' => $itemCount
        ]);
    
    }


    // you can delete the filter function this returns the filtered category 
    // of all the user in the db not the authenticated user

    public function filter(Request $request) {
        // from the optimized scoped method but we must evaluate what name is 
        // first we must db(request) to see what it comes in as
        // $testItem = Item::filterItem($request->name)->filterOrder($request->order);


    
        $fields = ["favorites", "health", "baby", "toys", "furniture", "electronics", "vehicles", "clothings"];
        
        foreach ($fields as $field) {

            if (request()->has($field)) {
                $value = request()->get($field);

                // $category =  Category::where('name', $value)->with('items')->withCount('items')->get();

                // $catyFiltered =  $category->items->filter(function ($item) use(&$category){
                //     if (auth()->id() ==  $item->user_id ) {
                //         return $category;
                //     }
                // });
               
                // return response()->json($catyFiltered);
                
                $catyItemsCount = Category::where('name', $value)->withCount('items')->get();
                            
                return response()->json($catyItemsCount);

                // you can delete the commented out part below 
                /*
                    // $caty = Category::where('name', $fields)->first();

                    $orders = ['title', 'newest', 'oldest'];


                    foreach($orders as $order){
                        if (request()->has($order)) {
                            $orderValue = request()->get($order);
                            // $filteredOrder =  Category::filterItem($value)->get();
                            // $filteredOrder =  Category::where('name', $value)->items;
                        
                            $filteredOrder =  Category::where('name', $value)
                                ->withCount('items')
                                ->get();

                            // pls test the two commented method below
                            // $filteredOrder =  Category::where('name', $value)
                            //     ->with('items')
                            //     ->filterOrder($orderValue)->get();
                            
                            // $itemPotential =  Category::where('name', $value)->items;
                            // $filteredOrder $itemPotential->filterOrder($orderValue)->get();
                            
                            return response()->json([...$filteredOrder, $caty]);
                        }
                    }

                */

                // foreach($orders as $order){
                //     if (request()->has($order)) {
                //         $orderValue = request()->get($order);
                //         echo 'I am running inside if';
                //         // $filteredOrder =  Category::filterItem($value)->items;
                //         // $filteredOrder =  Category::where('name', $value)->items;
                //         $filteredOrder =  Category::where('name', $value)->with('items')->get();
                //         //->filterOrder($orderValue)->get();;
                        
                
                        
                //         return response()->json([...$filteredOrder, "categoryname" => $caty]);
                //     }
                // }
            }
        }

        $orderType = ['title', 'newest', 'oldest'];

        foreach($orderType as $order) {
            if (request()->has($order)) {
                $value = request()->get($order);

                $catOrder = Category::filterOrder($order)->get();
                return response()->json($catOrder);
                // $catOrder = Category::where('user_id', $item->user->id)->filterOrder($order)->get();
            }
        }
        
        // You can delete the below
        
        //$items = Category::query();
        
        /* This is one way we can handle it give that 
        all request come in with the request->name not  $request->favorites, $request->health etc
       


        for ($fields of $field) {
            if ($field == $request->$field) {
                $items.where($field, $request->$field);
                // previously
                // $items.where($field, $request->$field);

            }
    
        }
        $items = $items->get();

        return response()->json($items);

        */



    }



    public function search(Request $request) {
        $user = auth()->user();
        $item = $request->items;
       
        /* first method; */
        $items = Item::query();

        $items->where(function($query) use ($item, $user) {
            // echo $item;
            $query->where('user_id', $user->id)
                ->where('title', 'LIKE', '%' . $item . '%')
                ->orWhere('subtitle', 'LIKE', '%' . $item . '%')
                ->orWhere('description', 'LIKE', '%' . $item . '%');
        });

        $items = $items->get();

        return response()->json($items);


    }
}
