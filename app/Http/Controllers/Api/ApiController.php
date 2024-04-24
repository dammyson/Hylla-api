<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Exceptions\AuthenticationException;

class ApiController extends Controller
{
    //

    public function register(Request $request) {

        try {
            //data validation
            $request->validate([
                "first_name" => 'required',
                "last_name" => 'required',
                "date_of_birth" => 'required',
                "phone_number" => "required|unique:users",
                "zip_code" => "required",
                "email" => "required|email|unique:users",
                "password" => "required|confirmed",
            ]);

            User::create([
                "name" => $request->first_name . ' ' . $request->last_name,
                "first_name" => $request->first_name,
                "last_name" => $request->last_name,
                "date_of_birth" => $request->date_of_birth,
                "phone_number" => $request->phone_number,
                "zip_code" => $request->zip_code,
                "email" => $request->email,
                "password" => Hash::make($request->password),
            ]);

            /*
                IMPORTANT! the commented code the below has not been tested yet (because twillio credential are  not available) however,
                it redirects to the generateOtp route and passing the email and password as parameters
                
                if this method fails (we try to pass the data as form data) then comment it and use the next one where variable as passed as route parameters

                return redirect()->route('otp.generate')->with([
                    'email' => $request->email, 
                    'password' => $request->password,
                ]);
                
                here while redirecting to the generate route pass in the variables as route parameters 
                // return redirect()->route('otp.generate', ['email' => $request->email, 'password' => $request->password, 'phone_no'=> $request->phone_no])->with('success','user registered successfully');
                
            */
        
            
            return response()->json([
                "status" => true,
                "message" => "User created successfully"
            ], 201);
        
        } catch(\Throwable $throwable) {
            $message = $throwable->getMessage();

            return response()->json([
                "status" => 'failed',
                "message" => $message
            ], 422);
        }

        

    }


    public function login(Request $request) {
        try {
            $request->validate([
                "email" => "required|email",
                "password" => "required"
            ]);
    
            // create error handle if your login fails
    
            /**
             * IMPORTANT|! if the twillio credential (for user otp) are ready, uncomment this and 
             * comment out the rest  others below. 
             * 
             * if the commented method fails, then convert the variable being pass using the 'with' 
             * method to route parameters
            */ 
            // return redirect()->route('otp.generate')->with([
            //     'email' => $request->email, 
            //     'password' => $request->password
            // ]);
    
            
            // if the twillio credential are ready then comment/delete these codes below
            if (!Auth::attempt([
                "email" => $request->email,
                "password" => $request->password
            ])) {
                
                throw new AuthenticationException();
                
            }

            $user = Auth::user();
            $token = $user->createToken("myToken")->accessToken;

            return response()->json([
                "status" => true,
                "message" => "Login successful",
                "access_token" => $token
            ], 201);
        
        } catch(\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;

            if ($throwable instanceof ValidationException) {
                $message = "please fill the provided fields";
                $statusCode = 422;
            }

            if ($throwable instanceof AuthenticationException) {
                $message = "invalid credentials";
                $statusCode = 401;

            }

            return response()->json([
                "status" => false,
                "message" => $message
            ], $statusCode);
        }

        
    }

    // Profile Api (GET)
    public function profile() {
        try {
            $userdata = Auth::user();

            return response()->json([
                "status" => true,
                "message" => "Profile data",
                "data" => $userdata
            ], 200);
        
        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            return response()->json([
                "status" => false,
                "message" => $message,
            ], 401);
        }
        
    }

    // Edit user profile 
    public function profileEdit(Request $request) {
        try {
            $userdata = Auth::user();

            if (!$userdata) {
                throw new AuthorizationException();
            }

            // This method works but it does resaves everything in the database
            // whereas the other alternative below tho is longer but is only changes
            // row data where the particular request was entered
            Auth::user()->update([
                "name" => $request->name ?? $userdata->name,
                "email" => $request->email ?? $userdata->email,
                "password" => $request->password ?? $userdata->password,
                "phone_number" => $request->phone_number ?? $userdata->phone_number,
                "zip_code" => $request->zip_code ?? $userdata->zip_code,
                "date_of_birth" => $request->date_of_birth ?? $userdata->date_of_birth
            ]);

            
            /* 
                alternative is below is longer but only queries the database for only
                information that was entered by user to change
            */
        

            $userUpdated = Auth::user();

            return response()->json([
                "updatedUser" => $userUpdated
            ], 200);

        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $statusCode = 500;

            if ($throwable instanceof AuthorizationException) {
                $message = 'not authorized';
                $statusCode = 403;
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
                return response()->json([
                    'status' => 'failed', 
                    'message' => $errorMessage
                ], 422);
                

            }

            return response()->json([
                'status'=> 'failed',
                'message' => $message
            ], $statusCode);
        }

        
    }
    public function logout() {
        try {
            auth()->user()->token()->revoke();
    
            return response()->json([
                "status" => true,
                "message" => "User logged out"
            ], 200);
        
        } catch (\Throwable $throwable) {
            return response()->json([
                'status'=> 'failed',
                'message' => "failed to log user out"
            ], 500);
        }

    }

    // I suggest we create a column called state and set it to false
    // instead of deleting the account, and only give this privelege
    // to an admin to be able to delete the account in case we later
    // need information from this account
    public function deleteAccount() {
        try {
            // Auth::user()
        auth()->user()->delete();
       
            return response()->json([
                "status" => true,
                "message" => "User account deleted successfully"
            ]);

        } catch (\Throwable $throwable) {
            return response()->json([
                'status'=> 'failed',
                'message' => "failed to delete user account"
            ], 500);
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
            $itemsTotal = $items->sum('price');// items sum
            $favoriteItemsCount = $items->where('favorite', true)->count(); 
            
            return response()->json([
                'itemsCount'=> $itemsCount,
                'favoriteItemsCount'=> $favoriteItemsCount,
                'itemsTotal' => $itemsTotal
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

    public function items() {
        try {
            $user = Auth::user();

            // Incase you want to display all items including the archived ones,
            // then comment the  code directly below and uncomment the following one
    
            $items = Item::where('user_id', $user->id)->where('archived', false)->select(['title', 'subtitle', 'created_at', 'id'])->get();
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

    public function addItem(Request $request){
        try {

            $request->validate([
                "categoryName" => 'required',
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


    public function updateItem(Request $request, Item $item) {
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


            return response()->json([$item], 200);

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

    public function estimatedItem(){
        try {
            $user = Auth::user();

            if (!$user) {
                throw new AuthorizationException('not authorized');

            }

            $items = Item::where('user_id', Auth::user()->id)
                ->select(['title', 'description', 'created_at', 'price'])
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

    public function 
    categories() {
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

}
