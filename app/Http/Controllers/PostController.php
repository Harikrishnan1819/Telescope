<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use GuzzleHttp\Client;

class PostController extends Controller
{
    public function postRequest(Request $request)
    {
        $client = new Client();
        $url = 'https://api.bugatlas.com/v1/logs/api';

        $headers = [
            'Content-Type' => 'application/json',
            'Api_key' => 'ON89EIUCFJSQYBBQ',
            'Secret_key'=> 'L645OQH1SF65D5FY',
        ];

        $data = [
       
        ];
 
        $postResponse = $client->post($url, [
            'headers' => $headers,
            'json' => $data,
        ]);

        $responseCode = $postResponse->getStatusCode();
        return response()->json(['response_code' => $responseCode]);
    }
}        