<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

}
