<?php

namespace App\Http\Controllers\Api;

use Throwable;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
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
                "email" => "required|email|unique:users",
                "password" => "required|confirmed",
                "firebase_token" => "nullable|string" 
            ]);

            $user =  User::create([
                "name" => $request->first_name . ' ' . $request->last_name,
                "first_name" => $request->first_name,
                "last_name" => $request->last_name,
                "firebase_token" => $request->firebase_token,
                "date_of_birth" => "2024-10-15 00:36:48",
                "phone_number" => "000000000",
                "zip_code" => "62704",
                "email" => $request->email,
                "password" => Hash::make($request->password),
            ]);
           
            $token = $user->createToken("myToken")->accessToken;
            
            return response()->json([
                "status" => true,
                "message" => "User created successfully",
                "access_token" => $token,
                "user" => $user
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
                "password" => "required",
                "firebase_token" => "nullable|string" 
            ]);
    

            // if the twillio credential are ready then comment/delete these codes below
            if (!Auth::attempt([
                "email" => $request->email,
                "password" => $request->password
            ])) {
                
                throw new AuthenticationException();
                
            }

            $user = Auth::user();
             // Update the firebase_token field if it is present in the request
             if ($request->has('firebase_token')) {
                $user->firebase_token = $request->firebase_token;
                $user->save();
            }
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

    public function googleRedirect(){
        return Socialite::driver('google')->redirect();
    }


    public function gooogleCallback() {
          // dd("I ran");
        try {
            $user = Socialite::driver('google')->stateless()->user();
          
        } catch (Throwable $e) {
            return response()->json([
                "error" => true,
                "message" => $e->getMessage()
            ], 500);
            // return redirect('/')->with('error', 'Google authentication failed.');
        }

        $existingUser = User::where('email', $user->email)->first();

        if ($existingUser) {
            $data['user'] =  $existingUser;
            $data['token'] =  $existingUser->createToken('Nova')->accessToken;
      
        } else {
            [$firstName, $lastName] = explode(" ", $user->name);
            $newUser = User::updateOrCreate([
                'email' => $user->email
            ], [
                   'name' => $user->name,
                    "date_of_birth" => "2024-10-15 00:36:48",
                    "phone_number" => "000000000",
                    "zip_code" => "62704",
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'password' =>  Hash::make(Str::random(16)), // Set a random password
              
            ]);


            $data['user'] =  $newUser;
            $data['token'] =  $newUser->createToken('Nova')->accessToken;
        }

        return response()->json([
            "error" => false,
            "data" => $data
        ], 200);
    }

    public function verifyGmail(Request $request) {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {

                $user = User::create([
                    'email' => $user->email,
                    'name' => $request->firstName . " " . $request->lastName,
                    "date_of_birth" => "2024-10-15 00:36:48",
                    "phone_number" => "000000000",
                    "zip_code" => "62704",
                    'first_name' => $request->firstName,
                    'last_name' => $request->lastName,
                    'password' =>  Hash::make(Str::random(16)), // Set a random password
                
                ]);
            }
        }  catch (\Throwable $throwable) {
            return response()->json([
                'status'=> 'failed',
                'message' => "failed to delete user account",
                'actual_message' => $throwable->getMessage()
            ], 500);
        }
       

        return response()->json([
            "success" => true, 
            "user" => $user
        ], 200);

    }
}
