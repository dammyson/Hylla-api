<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    //

    public function register(Request $request) {

        //data validation
        $request->validate([
            "first_name" => 'required',
            "last_name" => 'required',
            "date_of_birth" => 'required',
            "phone_no" => "required|unique:users",
            "zip_code" => "required",
            "email" => "required|email|unique:users",
            "password" => "required|confirmed",
        ]);

        User::create([
            "name" => $request->first_name . ' ' . $request->last_name,
            "first_name" => $request->first_name,
            "last_name" => $request->last_name,
            "date_of_birth" => $request->date_of_birth,
            "phone_no" => $request->phone_no,
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
        ]);

    }


    public function login(Request $request) {
        $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);

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
        if (Auth::attempt([
            "email" => $request->email,
            "password" => $request->password
        ])) {
            
            $user = Auth::user();
            $token = $user->createToken("myToken")->accessToken;

            return response()->json([
                "status" => true,
                "message" => "Login successful",
                "access_token" => $token
            ]);
        }

        return response()->json([
            "status" => false,
            "message" => "Invalid credentials"
        ]);
    }

    // Profile Api (GET)
    public function profile() {
        $userdata = Auth::user();

        return response()->json([
            "status" => true,
            "message" => "Profile data",
            "data" => $userdata
        ]);
    }

    // Edit user profile 
    public function profileEdit(Request $request) {
        // "first_name" => 'required',
        // "last_name" => 'required',
        // "date_of_birth" => 'required',
        // "zip_code" => "required",
        // "email" => "required|email|unique:users",
        // "password" => "required|confirmed",

        $userdata = Auth::user();

        // This method works but it does resaves everything in the database
        // whereas the other alternative below tho is longer but is only changes
        // row data where the particular request was entered
        Auth::user()->update([
            "name" => $request->name ?? $userdata->name,
            "email" => $request->email ?? $userdata->email,
            "password" => $request->password ?? $userdata->password,
            "phone_no" => $request->phone_no ?? $userdata->phone_no,
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
        ]);
    }
    public function logout() {
        
        auth()->user()->token()->revoke();

        return response()->json([
            "status" => true,
            "message" => "User logged out"
        ]);

    }

    // I suggest we create a column called state and set it to false
    // instead of deleting the account, and only give this privelege
    // to an admin to be able to delete the account in case we later
    // need information from this account
    public function deleteAccount() {
        // Auth::user()
        auth()->user()->delete();
       
        response()->json([
            "status" => true,
            "message" => "User created successfully"
        ]);
    }


    // all inventories page
    public function inventories() {
        $user = Auth::user();
        
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
        ]);
    
    }

    public function items() {
        $user = Auth::user();

        // Incase you want to display all items including the archived ones,
        // then comment the  code directly below and uncomment the following one

        $items = Item::where('user_id', $user->id)->where('archived', false)->select(['title', 'subtitle', 'created_at', 'id'])->get();
       // $items = Item::where('user_id', $user->id)->select(['title', 'subtitle', 'created_at', 'id'])->get();
        return response()->json($items);
    }

    public function addItem(Request $request){
        $user = Auth::user();
        


        // Important!!! this method is meant to create a new item when a barcode is scanned
        // I could tell which fields where optional and which were required, when this has been
        // decided pls remove the request->all() method and convert to the appropriate 
        // input field as shown in the commented section below

        $item = Item::create([...$request->all(), 'user_id' => $user->id]);

          
        // $item = $item->update([
        //     'title' => $request->title,
        //     'subtitle' => $request->subtitle,
        //     'description' => $request->description,
        //     'favorite' => $request->markedfavorite,
        //     'archived' => $request->archived,
        //     'product_name' => $request->productName,
        //     'product_number'=> $request->productNumber,
        //     'lot_number' => $request->lotNumber ,
        //     'barcode' => $request->barcode 
        //     'weight'=> $request->weight        
        //     'dimension' => $request->dimension 
        // ]);

        return response()->json([
            "status"=> "succesful",
            "item" => $item
        ]);
    }

    public function addCategory(Request $request) {
        $request->validate([
            "name" => 'required',
        ]);

        $category = Category::create(["name"=>$request->name]);
       

        return response()->json([
            "status" => "successfully",
            "category" => $category
        ]);
    }

    public function favorite() {

        $user = Auth::user();

        $favoriteItems = Item::where('user_id', $user->id)
            ->where('favorite', true)
            ->select(['title', 'subtitle', 'created_at', 'id'])->get();
            
        return response()->json($favoriteItems);
    }


    public function item(Item $item) {
        $itemOwnerId  = $item->user_id;
        $user = Auth::user();
        
        if ( $itemOwnerId !== $user->id ) {
            return response()->json([
                'status' => 'failed',
                'message' => "not authorized"
            ]);
        }

        return response()->json([
            'item' => $item
        ]);
       
    }


    public function updateItem(Request $request, Item $item) {
        
        $itemOwnerId  = $item->user_id;
        $user = Auth::user();

        if ( $itemOwnerId !== $user->id ) {
            return response()->json([
                'status' => 'failed',
                'message' => "not authorized"
            ]);
        }
        
        $item = $item->update([
            'title' => $request->title ?? $item->title,
            'subtitle' => $request->subtitle ?? $item->subtitle,
            'description' => $request->description ?? $item->description,
            'favorite' => $request->markedfavorite ?? $item->favorite,
            'archived' => $request->archived ?? $item->archived,
            'product_name' => $request->productName ?? $item->product_name,
            'product_number'=> $request->productNumber ?? $item->product_number,
            'lot_number' => $request->lotNumber ?? $item->lot_number,
            'barcode' => $request->barcode ?? $item->barcode,
            'weight'=> $request->weight ?? $item->weight,
            'dimension' => $request->dimension ?? $item->dimension,
            'warranty_length' => $request->weight ?? $item->dimension,
        ]);

        return response()->json([$item]);

    }

    public function archivedItem() {
        $user = Auth::user();

        $archItem = Item::where('user_id', $user->id)
            ->where('archived', true)
            ->select(['title', 'subtitle', 'created_at', 'id'])
            ->get();

        return response()->json($archItem);
    }

    public function estimatedItem(){
        $items = Item::where('user_id', Auth::user()->id)
            ->select(['title', 'description', 'created_at', 'price'])
            ->orderBy('price', 'desc')
            ->get();

        return response()->json($items);
    }

    public function 
    categories() {
        // this is considered the most optimized method from all the methods listed
        // there are severally method below, you can use to just uncomment them as you please
        // 
        
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
   
       
    }



}
