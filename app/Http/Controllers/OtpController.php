<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class OtpController extends Controller
{
    /**
     * return the view page where the user enters email and password
     */
    
    // for registration the only difference is that  we dont redirect start to the
    // otp login rather, we call the generateOtp route and pass the email and password as parameters

    public function otpLogin() {

        // this view should render the login page for user to input email and password
        return view('auth.otpLogin');
    }

    // generate Otp
    public function otpGenerate(Request $request) {
        $request->validate([
            "email" => 'required',
            'password'=> 'required'
         
        ]);

        $user = User::where('email', $request->email)->where('password', $request->password)->first();
        
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message'=> 'no such user'
            ]);
        }


        $mobileNum = $user->phone_number;

        $userOtp = $this->generateOtp($request->mobileNum);
        $userOtp->sendSMS($mobileNum);    
        
        return response()->json([
            'status'=> 'success',
            'message' => 'OTP sent',
            'user_id' => $userOtp->id
        ]);

        // return redirect()->route('otp.verification', ['user_id' => $userOtp->user_id])
        //     ->with('success','OTP has been sent to Your Mobile number.');
    }

    private function generateOtp($mobile_no)
    {
        $user = User::where('mobile_no', $mobile_no)->first();
        // arrange the otp by the last entered one in the db in ascending order
        $userOtp = UserOtp::where('user_id', $user->id)->latest()->first();
        
        // get current timestamp
        $now = now();

        if ($userOtp && $now->isBefore($userOtp->expire_at)) {
            return $userOtp;

        }

        /* Create a New OTP */
        return UserOtp::create([
            'user_id' => $user->id,
            'otp' => rand(123456, 999999),
            'expire_at' => $now->addMinutes(10)
        ]);

    
    }

    /**
     * display page show otp input
     * and pass the user Id along
     */
    public function Otpverification($user_id) {
        return view('auth.otpVerification')->with([
            'user_id' => $user_id
        ]);
    }


    /**
     * here we validate that the user's entered otp
     * is the same with that in the db and if true, login the user in.
     */

    public function loginWithOtp(Request $request) {
        // validation
        $request->validate([
            'user_id' => 'required|exists:users, id',
            'otp'=> 'required',
        ]);


        /* Validation Logic */
        // $userOtp = UserOtp::where('user_id', $request->user_id)->where('otp', $request->otp)->latest()->first()
        $userOtp = UserOtp::where('user_id', $request->user_id)->where('otp', $request->otp)->first();
        
        $now = now();

        if (!$userOtp) {
            return redirect()->back()->with('error', 'your otp is incorrect');
        
        } else if ($userOtp && $now->isAfter($userOtp->expire_at)) {
            return redirect()->route('otp.login')->with('error', 'your otp has expired');
        }

        /*
            the method  commented out so we the test the method in 'SECTION 2' below
            however if section 2 fails (because Auth::login($user) does not give us a session access token rather
            if gives us an Id and other many route depend on this token to be accessed) then comment section 2 out
            / and use the commented method below


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

        */
        
        $user = User::whereId($request->user_id)->first();

        if ($user) {
            $userOtp->update([
                'expire_at' => now()
            ]);

            Auth::login($user);
            
            $sessionId = session()->getid();
            return response()->json([
                        "status" => true,
                        "message" => "Login successful",
                        "sessionId" => $sessionId
            ]);
        }
    }
}
