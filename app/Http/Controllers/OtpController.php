<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Services\User\CreateOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use \Mailjet\Resources;


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

    public function SendMail(Request $request){
  
        $validated = $request->validate([
            'body' => 'required',
            'subject' => 'required',
        ]);

        $user = Auth::user();
       $mj = new \Mailjet\Client("fe741e62dc1ee2ae5edb9bbb02729f6b", "3697916ea1f2432e788ca2278f284334",true,['version' => 'v3.1']);

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "ahall@bearwood.llc",
                        'Name' => "Hylla Support"
                    ],
                    'To' => [
                        [
                            'Email' => "support@hylla.app",
                            'Name' => "Hylla Support"
                        ]
                    ],
                    'Subject'=> $validated["subject"],
                    'TextPart'=> $validated["body"],
                    'HTMLPart'=> "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                        <h2 style='color: #4CAF50;'>Hello Support Team,</h2>
                        <p><b>Issues Reported by the User:</b></p>
                        <p style='margin: 10px 0; padding: 10px; background-color: #f9f9f9; border-left: 4px solid #4CAF50;'>
                            " . nl2br(htmlspecialchars($validated["body"])) . "
                        </p>
                        <p>If you have any questions or need further questins, please feel free to contact the user at <a href='mailto:$user->email'>
                        " . nl2br(htmlspecialchars($user->name)) . "
                        </a>.</p>
                        <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
                       
                        <p style='font-size: 0.9em; color: #777;'>This email is generated automatically, please do not reply directly to this email.</p>
                    </div>
                "
                ]
            ]
        ];

        $response = $mj->post(Resources::$Email, ['body' => $body]);

        // Read the response
         if($response->success()){
            return response()->json(['status' => true,  'message' => 'Mail sent successfully'], 201);
         }
        

     }
}
