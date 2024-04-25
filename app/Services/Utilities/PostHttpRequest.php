<?php

namespace App\Services\Utilities;

use App\Services\BaseServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PostHttpRequest implements BaseServiceInterface
{
    protected $url, $body;

    public function __construct($url, $body)
    {
        $this->url = $url;
        $this->body = $body;
    }

    public function run()
    {

       try {
              Log::info(":::::::::::::::::::::::::::::STARTED API CALL");
              $response = Http::withToken(env('TOKEN', "this"))
              ->withHeaders(['Content-Type' => 'application/json', 'accept' => 'application/json'])
              ->post($this->url,  $this->body);

               $c1 = $response->failed();
               $c2 = $response->clientError();
               $c3 = $response->serverError();

            
            if ($c1 or $c2 or $c3){
                Log::info("::::::::::::::::::::::::::::: API CALL FAIL WITH Errors ");
                return false;
              } else{
                Log::info($response->body());
                Log::info("::::::::::::::::::::::::::::: API CALL FINISHED SUCCESFULY");
                return json_decode($response->body());
              }
               
             
        } catch (\Exception  $e) {
            Log::info($e->getMessage());
            
            // return false;
        }
        
    }  
}