<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    //

    public function showForgotPasswordForm() {

        //we render a view page here with a form that request for the
        // users email and the submit fires a post method in the sendMail route
    
        return view('password.forgotPasswordForm');
    }

    public function forgotPasswordPost(Request $request) {
        try {
            /* we can validate the user exist in the database as */
            $request->validate([
                'email' => 'required|email|exists:users'
            ]);

            $user = User::where('email', $request->email)->first();
        
            $to_name = $user->name;
            $to_email = $user->email;

            // $token = randomFunctionToGenerateToken();
            // can we but an expiration time on this token?
            $token = Str::random(64);

            $checkEmailToken = DB::table('password_reset_tokens')->where(['email' => $request->email])->first();
            if ($checkEmailToken) {

                DB::table('password_reset_tokens')->where(['email' => $request->email])->update([
                    "token" => $token,
                    'created_at' => Carbon::now()
                ]); 

                // DB::table('password_reset_tokens')->where(['email' => $request->email])->delete(); 
            } else {
                // store the token in the password reset table in the database
                // find out a way to make this token in this database to expire 
                // in 10mins;
                DB::table('password_reset_tokens')->insert([
                    'email' => $request->email,
                    'token' => $token,
                    'created_at' => Carbon::now()
                ]);
            }

            

            $data = array('name' => $to_name,
                'body' => "Reset password from Hyla",
                'token' => $token
            );

            //alternative we can declare as

            // $data = ['name' => $to_name, 'body' => 'msg body', 'token' => $token];

            Mail::send('sendmail', $data, 
                function($message) use($to_name, $to_email) {
                    $message->to($to_email, $to_name);
                    $message->subject('Reset Password mail');
                    // This line below might not be necessary as they are 
                    // already configured from our config/mail.php script
                    //$message->from('Ocelot Group', 'Mail to reset password');
                }

            );

            return response()->json([
                "name" => $to_name,
                "body" => "email sent to {{$to_email}}",
                "token" => $token

            ]);
            // return redirect()->to(route('login.get'))->with('success', "we have sent an email to reset password");

        }  catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            return response()->json([
                'status' => 'failed',
                'message' => $message
            ]);
        }
    }
        
}
