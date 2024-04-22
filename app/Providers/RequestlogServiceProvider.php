<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use stdClass;



class RequestlogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Request $request)
    {
        $time = Carbon::now('Asia/Kolkata');
        $formattedTime = $time->format('F jS Y, h:i:s A');
        $hostname = gethostname();
        $method = $request->method();
        $path = $request->path();
        $ipAddress = $request->ip();
        $startTime = microtime(true);
        $duration = round((microtime(true) - $startTime) * 1000, 2); 
        $memoryUsage = memory_get_usage(true) / (1024 * 1024); // in MB
        $userAgent = $request->header('user-agent');
        $headers = $request->header();
        $headersObject = new stdClass();

        foreach ($headers as $key => $value) {
            $headersObject->{$key} = $value[0];
        }
        $response = Http::get(config('app.url'));
        $response->body();

        
        // dd($response);
        // $value = $request->session()->get('key');
        // dd($headers);
        // $cookies = $request->cookies();
        // $laravelSessionCookie = $request->cookie('laravel_session');


        // dd($laravelSessionCookie);
    
        // Log the details
        $logDetails = ([
            'Time' => $formattedTime,
            'Hostname' => $hostname,
            'Method' => $method,
            'Path' => $path,
            'Status' => $response->status(),
            'IP Address' => $ipAddress,
            'Duration' => "{$duration} ms",
            'Memory usage' => "{$memoryUsage} MB",
            'User-agent' => $userAgent,
            'HEADERS'=> $headersObject,
            // 'SESSION'=> $value,
        ]);
    // dd($logDetails);
    }
}
