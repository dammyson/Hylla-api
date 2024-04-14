<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Twilio\Exceptions\ConfigurationException;
use Exception;
use Twilio\Rest\Client;

class UserOtp extends Model
{
    use HasFactory;

    protected $fillable = ['otp', 'user_id', 'expire_at'];
    public function users() {
        $this->belongsTo(User::class);
    }

    public function sendSMS($receiverNumber)
    {
        $message = "Login OTP is ". $this->otp;

        try {
            $account_sid = getenv('TWILIO_SID');
            $auth_token = getenv('TWILIO_TOKEN');
            $twilio_number = getenv('TWILIO_FROM');
        
            $client = new Client($account_sid, $auth_token);
            $client->messages->create($receiverNumber, [
                'from' => $twilio_number,
                'body' => $message
            ]);

            info('SMS sent successfully.');
        
        } catch(exception $e) {
            info('Error: '.  $e->getMessage());
        }
    }
}
