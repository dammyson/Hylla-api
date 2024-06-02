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
}
