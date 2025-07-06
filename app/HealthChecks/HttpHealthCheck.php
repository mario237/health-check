<?php

namespace App\HealthChecks;

use Exception;
use Illuminate\Support\Facades\Http;

class HttpHealthCheck extends BaseHealthCheck
{
    public function check(): array
    {
        $start = microtime(true);

        try {
            $appUrl = config('app.url');
            $timeout = 10;

            $response = Http::timeout($timeout)->get($appUrl);

            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

            if ($response->successful()) {
                return $this->formatResult(
                    'ok',
                    'Application is accessible via HTTP',
                    $time,
                    [
                        'url' => $appUrl,
                        'status_code' => $response->status(),
                        'response_time_ms' => $time
                    ]
                );
            }

            return $this->formatResult(
                'failed',
                "HTTP check failed with status {$response->status()}",
                $time,
                [
                    'url' => $appUrl,
                    'status_code' => $response->status(),
                    'error' => $response->body()
                ]
            );
        } catch (Exception $e) {
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';
            return $this->formatResult('failed', 'HTTP check failed: ' . $e->getMessage(), $time, ['error' => $e->getMessage()]);
        }
    }
}
