<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Services\User\CreateOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class OtpController extends Controller
{
    
    public function generatePinForgetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required',
        ]);
      
        try {
            $user = User::where('email', $validated['email'])->first();
            $new_otp = new CreateOtpService($validated);
            $new_otp = $new_otp->run();

            $user_mail_content_array = array(
                "sender" => "PR",
                "code" => $new_otp->otp,
                "link" => "",

              );

             
            $user->notify(new ResetPasswordNotification($user_mail_content_array));
           
            return response()->json(['status' => true, 'data' => $new_otp,  'message' => 'Mail sent successfully'], 201);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return response()->json(['status' => false,  'message' => $exception->getMessage()], 500);
        }
    }

    public function VerifyOTP(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required',
            'code' => 'required',
        ]);
        
        try {
            $model = Otp::where('email', '=', $validated['email']) 
            ->where('otp', '=', $validated['code'])
            ->where('is_verified', '=', 0)
            ->firstOrFail();
            if($model->created_at->addMinutes(30)->isPast()) {
                return response()->json(['status' => false,  'message' => "This OTP has expired"], 500);
            }
            $model->is_verified = 1;
            $model->save();

            return response()->json(['status' => true, 'data' => $model, 'message' => 'Verified successfully'], 200);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return response()->json(['status' => false,  'message' => "This OTP has expired or does not exist"], 500);
        }
    }

    public function SendMail(){
        // Use your saved credentials, specify that you are using Send API v3.1

       // $mj = new \Mailjet\Client("011c044df1b2023fbb1c98f81590ebc6", "3b0c93b357a548a0cc8c181012c60ff7",true,['version' => 'v3.1']);

        // Define your request body



        // $body = [
        //     'Messages' => [
        //         [
        //             'From' => [
        //                 'Email' => "simonsmith850@hotmail.com",
        //                 'Name' => "Me"
        //             ],
        //             'To' => [
        //                 [
        //                     'Email' => "ayeni.ayobami21@gmail.com",
        //                     'Name' => "You"
        //                 ]
        //             ],
        //             'TemplateID' => 5678093,
        //             'TemplateLanguage' => true,
        //             'Subject' => "Your email flight plan!"
        //         ]
        //     ]
        // ];

        // // All resources are located in the Resources class

        // $response = $mj->post(Resources::$Email, ['body' => $body]);

        // // Read the response
        //  if($response->success()){
        //     return response()->json(['status' => true,  'message' => 'Mail sent successfully'], 201);
        //  }
        

     }
}
