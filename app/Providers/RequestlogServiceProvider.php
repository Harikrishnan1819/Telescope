<?php

namespace App\Providers;

use App\Traits\HeadersTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use stdClass;



class RequestlogServiceProvider extends ServiceProvider
{
    use HeadersTrait;
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
        $logDetails = $this->prepareLogDetails($request);
        $this->sendLogToApi($logDetails);
    }

    private function prepareLogDetails(Request $request)
    {
        $time = Carbon::now();
        $formattedTime = $time->format('F jS Y, h:i:s A');
        $startTime = $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);
        $memoryUsage = memory_get_usage(true) / (1024 * 1024);

        $headers = $request->header();
        $headersObject = new stdClass();
        foreach ($headers as $key => $value) {
            $headersObject->{$key} = $value[0];
        }
        $response = Http::get(config('app.url'));

        // Log the details
        return [
            'Request_url' => $request->fullUrl(),
            'Time' => $formattedTime,
            'Hostname' => gethostname(),
            'Method' => $request->method(),
            'Path' => $request->path(),
            'Status' => $response->status(),
            'IP Address' => $request->ip(),
            'Duration' => "{$duration} ms",
            'Memory usage' => "{$memoryUsage} MB",
            'User-agent' => $request->header('user-agent'),
            'HEADERS' => $headersObject,
        ];
    }

    private function sendLogToApi($logDetails)
    {
        $headers = $this->getApiHeaders();

        $payload = [
            "request_url" => $logDetails['Request_url'],
            "request_method" => $logDetails['Method'],
            "payload" => 'Payload',
            "tag" => "config",
            "meta" => [
                "meta" => $logDetails,
            ]
        ];
            $endPoint = 'logs/api';
        $response = $this->processApiResponse($endPoint, $payload);
        dd(json_decode($response->body()));
    }
}