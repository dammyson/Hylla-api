<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Laravel\Passport\Exceptions\AuthenticationException;

class CategoryController extends Controller
{
    // Get categories with Item count
    public function categories()
    {
        // this is considered the most optimized method from all the methods listed
        // there are severally method below, you can use to just uncomment them as you please
        // 
        try {
            $user = Auth::user();
            $userId =  $user->id;
            $categoriesWithProductCount = Category::whereHas('products', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->withCount(['products' => function ($query) use ($userId) {
                    $query->where('user_id', $userId) 
                    ->where('archived', false);
                }])
                ->get();

            return response()->json([
                'status' => 'success',
                'message' =>  $categoriesWithProductCount
            ], 200);
        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;


            return response()->json([
                'status' => 'failed',
                'message' => $message
            ], $statusCode);
        }
    }

    public function addCategory(Request $request) {

        $category = Category::create([
            'name' => $request->input('name')
        ]);

        return response()->json([
            'message' => "category created successfully",
            'category' => $category
        ]);
    }


    public function item($id)
    {
        try {
            $user = Auth::user();
            $userId =  $user->id;
            $categoryId = $id;

            $category = Category::where('id', $categoryId)
            ->with(['products' => function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('archived', false)
                      ->with('stores', 'images'); // Eager load both stores and images relationships
            }])
            ->first();

            return response()->json([
                'status' => 'success',
                'item' => $category
            ]);

        } catch (\Throwable $exception) {
            $message = $exception->getMessage();
            $statusCode = 500;

            if ($exception instanceof AuthenticationException) {
                $message = 'not authorized';
                $statusCode = 401;
            }

            return response()->json([
                'status' => 'failed',
                'message' => $message
            ], $statusCode);
        }
    }

    
    public function updateCategory(Request $request, $id) {
        $validated = $request->validate([
            'title' => 'string|nullable',
            'code' => 'string|nullable',
            'description' => 'string|nullable',
            'serial_number' => 'string|nullable',
            'barcode_number' => 'string|nullable',
            'barcode_formats' => 'string|nullable',
            'mpn' => 'string|nullable',
            'model' => 'string|nullable',
            'asin' => 'string|nullable',
            'category' => 'string|nullable',
            'manufacturer' => 'string|nullable',
            'brand' => 'string|nullable',
            'age_group' => 'string|nullable',
            'ingredients' => 'string|nullable',
            'nutrition_facts' => 'string|nullable',
            'energy_efficiency_class' => 'string|nullable',
            'color' => 'string|nullable',
            'gender' => 'string|nullable',
            'material' => 'string|nullable',
            'pattern' => 'string|nullable',
            'multipack' => 'string|nullable',
            'size' => 'string|nullable',
            'length' => 'string|nullable',
            'width' => 'string|nullable',
            'height' => 'string|nullable',
            'weight' => 'string|nullable',
            'release_date' => 'string|nullable',
            'last_update' => 'string|nullable',
            'warranty_length' => 'string|nullable',
            'dimension' => 'string|nullable',
            'category_ids' => 'array|required',
            'category_ids.*' => 'string'
        ]);

        $product = Product::with('categories')->findOrFail($id);
        
        $product->update($request->except('category_ids'));

        $product->categories()->sync($validated['category_ids']);

        return response()->json([
            'mesasge' => 'Product updated successfully',
            'product' => $product->load('categories'),
        ]);
    }

    public function addProductToCategory($categoryId, $productId) {
        $category = Category::findOrFail($categoryId);
        $category->products()->syncWithoutDetaching([$productId]);

        return response()->json([
            "error" => false,
            "category_products" => $category->products
        ]);
    }

    public function removeProductFromCategory($categoryId, $productId)
    {
        $category = Category::findOrFail($categoryId);
        $category->products()->detach($productId);
        
        return response()->json([
            "error" => false,
            "category_products" => $category->products
        ]);
    }
}
