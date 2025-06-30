<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Laravel\Passport\Exceptions\AuthenticationException;

class FilterController extends Controller
{
    public function orderCategory(Request $request) {
        try {
            $request->validate([
                'order_by' => 'required|string', // Assuming the form field is named 'order_by'

            ]);

            // Get the authenticated user
            $user = Auth::user();
            $categories = Category::whereHas('products', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with('products', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->get();
           
            if ($request->order_by) {
                $orderBy = $request->order_by;

                // Apply ordering
                if ($orderBy == 'asc') {
                    $categories = $categories->sortBy('created_at');

                } else if ( $orderBy == 'desc') {
                    $categories = $categories->sortByDesc('created_at');

                } else if ($orderBy == 'name' ) {
                    $categories = $categories->sortBy('name');
                }

                // Get item count for each category
               
            }

            $categoryData = $categories->map(function ($category) use ($user) {
                return [
                    'category' => $category,
                    'item_count' => $category->products->count()
                ];
            });

            return response()->json($categoryData);

        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;

            if ($throwable instanceof AuthorizationException) {
                $message = 'not authorized';
                $statusCode = 403;
            }
            
            return response()->json([
                'status' => 'failed',
                'message' => $message
            ], $statusCode);

        }
    }

    public function filterCategory(Request $request)
    {
        try {
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
            // $itemCount = $user->items()->where('category_id', $category->id)->count();

           // Count of the authenticated user’s items that sit in a given category
            $itemCount = $user->items()
                ->whereHas('product.categories', function ($q) use ($category) {
                    $q->where('categories.id', $category->id);
                })
                ->count();

            return response()->json([
                'category' => $category,
                'item_count' => $itemCount
            ], 200);
        
        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;

            if ($throwable instanceof AuthenticationException) {
                $message = 'user is not authenticated';
                $statusCode = 401;
             }
            return response()->json([
                'status' => 'failed',
                'message' => $message
            ], $statusCode);

        }
       
    
    }



    public function search(Request $request) {
        try {

            // Query products where any of the fields contain the search term (case-insensitive match from beginning)
            $items = Product::where('user_id', $request->user()->id)
                ->whereAny([
                    'title',
                    'description',
                    'barcode_number',
                    'barcode_formats',
                    'mpn',
                    'model',
                    'asin',
                    'title',
                    'category',
                    'manufacturer',
                    'serial_number',
                    'weight',
                    'dimension',
                    'warranty_length',
                    'brand',
                    'ingredients',
                    'nutrition_facts',
                    'size',
                    'description',
                    'code',
                    'barcode_number'
                ], 'LIKE', '%' . $request["item"] . '%')  // use %...% to match anywhere in the string
            ->get();


        return response()->json($items, 200);
        
        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;

            if ($throwable instanceof AuthenticationException) {
                $message = 'user is not authenticated';
                $statusCode = 401;
            }
            
            return response()->json([
                'status' => 'failed',
                'message' => $message
            
            ], $statusCode);
        }


    }
}
