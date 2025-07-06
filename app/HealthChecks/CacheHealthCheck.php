<?php

namespace App\HealthChecks;

use Exception;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class CacheHealthCheck extends BaseHealthCheck
{
    public function check(): array
    {
        $start = microtime(true);

        try {
            $testKey = 'health_check_cache_' . time();
            $testValue = 'test_value_' . random_int(1000, 9999);

            // Test cache write
            Cache::put($testKey, $testValue, 60);

            // Test cache read
            $retrieved = Cache::get($testKey);

            // Test cache delete
            Cache::forget($testKey);

            if ($retrieved !== $testValue) {
                throw new RuntimeException('Cache read/write test failed');
            }

            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

            return $this->formatResult(
                'ok',
                'Cache is working properly',
                $time,
                [
                    'driver' => config('cache.default'),
                    'store' => Cache::getStore()::class
                ]
            );
        } catch (Exception $e) {
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';
            return $this->formatResult('failed', 'Cache check failed: ' . $e->getMessage(), $time, ['error' => $e->getMessage()]);
        }
    }
}
