<?php

namespace App\Services\Utilities;

use App\Services\BaseServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetHttpRequest implements BaseServiceInterface
{
    protected $url;
    protected $token;

    public function __construct($url, $token=false )
    {
        $this->url = $url;
        $this->token = $token;
    }

    public function run()
    {
        $token = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxMTAxMDEsInVzZXJfdHlwZSI6NSwidHlwZSI6ImFjY2VzcyIsImV4cCI6MTczMDk2MTE1OCwiaWF0IjoxNzMwOTUzOTU4LCJuYmYiOjE3MzA5NTM5NTh9.D3gats3Oug9yesfXDWDBk2d5yrIvTmryed1rQJWWBVMrVJJO4a2DOUXSvfpinQfgdcqOlz1q3SyUvvUYQ0R6tUs_9x6S3OHgUmyDSCgK3EGn3CZIElqo62WSToKG1XUP9OztvQWjYPQA5eThSqLdf61NSkosOSMZ8AymQEJPE79QpWxnlyZu8GzfnASf9p27Ghkm2dmg_byIw20Uv_A0CGUYPww3xZjQ9tSoQhHAAFXrGgk1N2Ya3FAIzzc1mAk33nxRTuuXXCAOkXWezkpEqbOpvd7atHeHxOViPLJ-FvmLeO6FA4ZJT51KEpGF3DMu2b_y_lS5s7FraozojKTXIg';  
        try {
               // Set up base headers
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ];
            
            // Conditionally add the Authorization header if a token is provided
            if ($this->token) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }
            // Make the HTTP GET request with headers
              $response = Http::withHeaders($headers)->get($this->url);

              $c1 = $response->failed();
              $c2 = $response->clientError();
              $c3 = $response->serverError();

           if ($c1 or $c2 or $c3){
               Log::info("::::::::::::::::::::::::::::: API CALL FAIL WITH Errors ");
               return false;
             }else{
               Log::info($response->body());
               Log::info("::::::::::::::::::::::::::::: API CALL FINISHED SUCCESFULY");
               return json_decode($response->body());
             }
              
        } catch (\Exception $e) {
            return false;
        }
        
    }  
}