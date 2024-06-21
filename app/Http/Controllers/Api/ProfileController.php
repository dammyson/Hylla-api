<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Exception;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfileController extends Controller
{
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
        $request->validate([
            'email'=> 'sometimes|required|email',
            'password' => 'sometimes|required|string',
            'phone_number' => 'sometimes|required|string',
            'zip_code' => 'sometimes|required|string'
        ]);


        try {

            $userdata = Auth::user();

            if (!$userdata) {
                throw new AuthorizationException();
            }

            // This method works but it does resaves everything in the database
            // whereas the other alternative below tho is longer but is only changes
            // row data where the particular request was entered
            Auth::user()->update([
                "email" => $request->email ?? $userdata->email,
                "password" => $request->password ?? $userdata->password,
                "phone_number" => $request->phone_number ?? $userdata->phone_number,
                "zip_code" => $request->zip_code ?? $userdata->zip_code,
                // "date_of_birth" => $request->date_of_birth ?? $userdata->date_of_birth
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

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            // 'old_password' => 'required',
            'password' => 'required|between:4,32|confirmed',
        ]);

        try {
            $user = Auth::user();
            $info = User::where('id', $user->id)->first();
            if ($info) {

                // if (Hash::check(trim($validated['old_password']), $info->password)) {
                    $info->password = $validated['password'];
                    $info->save();
                    return response()->json(['status' => true, 'data' => $info,  'message' => 'Password Changed'], 201);
                // } else {
                //     return response(['message' => 'Email or Password Incorrect'], 401);
                // }
            } else {
                return response(['message' => 'Email or Password Incorrect'], 401);
            }
        } catch (Exception $exception) {
            return response()->json(['status' => false,  'error' => $exception->getMessage(), 'message' => 'Error processing request'], 500);
        }
    }


    
}
