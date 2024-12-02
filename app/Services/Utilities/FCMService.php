<?php

namespace App\Services\Utilities;

use App\Models\FirebaseToken;
use Google\Auth\Credentials\ServiceAccountJwtAccessCredentials;
use Carbon\Carbon;

class FCMService
{
    private $serviceAccountPath;

    public function __construct()
    {
        $this->serviceAccountPath = base_path(env('FIREBASE_CREDENTIALS', 'storage/app/firebase/service-account.json'));
    }

    public function getValidAccessToken(): string
    {
        // Check if a valid token exists
        $token = FirebaseToken::orderBy('expires_at', 'desc')->first();

        if ($token && Carbon::now()->lessThan($token->expires_at)) {
            return $token->access_token; // Return valid token
        }

        return $this->generateNewAccessToken();
    }

    private function generateNewAccessToken(): string
    {
        try {
            $credentials = new ServiceAccountJwtAccessCredentials(
                $this->serviceAccountPath,
                ['https://www.googleapis.com/auth/firebase.messaging']
            );

            $tokenData = $credentials->fetchAuthToken();
            $accessToken = $tokenData['access_token'];
            $expiresAt = Carbon::now()->addSeconds($tokenData['expires_in']); // Set expiration

            // Save the token in the database
            FirebaseToken::create([
                'access_token' => $accessToken,
                'expires_at' => $expiresAt,
            ]);

            return $accessToken;
        } catch (\Exception $e) {
            throw new \Exception('Failed to generate new access token: ' . $e->getMessage());
        }
    }
}
