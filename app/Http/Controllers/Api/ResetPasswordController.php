<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{

    public function resetPasswordPost(Request $request) {
       try {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required'
        ]);

        $resetPasswordToken =  DB::table('password_reset_tokens')->where([
            'email' => $request->email,
            "token" => $request->token
            ])->first();


        if (!$resetPasswordToken) {

           throw new AuthenticationException('Invalid Credentials');
        }

        User::where("email", $request->email)->update(['password' => Hash::make($request->password)]);
    
        DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();
        

        $updatedUser = User::where("email", $request->email)->first();
        $hashedPassword = Hash::make($request->password);

        return response()->json([
            "email" => $updatedUser->email,
            "password" => $updatedUser->password,
            "unhashedPassword" => $request->password,
            "hashedPassword" => $hashedPassword
   
        ], 200);
    
    } catch (\Throwable $throwable) {
        $message = $throwable->getMessage();
        $statusCode = 500;

        if ($throwable instanceof AuthenticationException) {
            $message = 'Invalid Credentials';
            $statusCode = 401;
        }

        if ($throwable instanceof ValidationException) {
            $message = 'error in form data';
            $statusCode = 422;
        }
        return response()->json([
            'status' => 'failed',
            'message' => $message
        
        ], $statusCode);
    }
        

        //return redirect()->to(route('login'))->with('success', 'Password reset successfully');
    
    }

    public function resetPassword(Request $request){
     
        $this->validate($request, [
            'password' => 'required|between:4,32|confirmed',
            'email' => 'required|exists:users,email'
        ]);

        try {
            $authUser = User::where('email', $request->email)->firstOrFail();
            $authUser->password = $request->password;
            $authUser->save();
          
            return response()->json(['status' => true, 'message' => 'Password Successfully Reset', 'data' => $authUser ], 200);
        } catch (\Exception $exception) {
            return response()->json(['status' => false,  'message' => $exception->getMessage()], 500);
        }

    }
}
