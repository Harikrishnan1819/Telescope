<?php

namespace App\Providers;

use Illuminate\Support\Facades\Crypt;
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
        $time = Carbon::now();
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
        ]);
        $encryptedLogDetails = Crypt::encrypt($logDetails);

        $headers = [
            'api_key' => config('app.api_key'),
            'secret_key' => config('app.secret_key'),
            'Content-Type' => 'application/json'
        ];
        $payload = [
            "request_url" => "/v1/projects",
            "request_method" => $method,
            "payload" => "payload(req-body)",
            "tag" => "config",
            "meta" => [
                "meta" => $encryptedLogDetails,
            ]
        ];
        // $decrypt = Crypt::decrypt($encryptedLogDetails);
        // dd($decrypt);
        $response = Http::withHeaders($headers)
                ->post('https://api.bugatlas.com/v1/logs/api', $payload);
        dd(json_decode($response->body()));

        // echo $response->body();


    }
}
