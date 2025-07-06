<?php

namespace App\HealthChecks;

use Exception;
use Illuminate\Support\Facades\Redis;
use RuntimeException;

class RedisHealthCheck extends BaseHealthCheck
{
    public function check(): array
    {
        $start = microtime(true);

        try {
            $redis = Redis::connection();

            // Test Redis connection by setting and getting a test value
            $testKey = 'health_check_' . time();
            $redis->set($testKey, 'test', 'EX', 10); // Set with 10 second expiry
            $result = $redis->get($testKey);
            $redis->del($testKey); // Clean up

            if ($result !== 'test') {
                throw new RuntimeException('Redis read/write test failed');
            }

            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

            $config = config('database.redis.default');

            return $this->formatResult(
                'ok',
                'Redis connection is working',
                $time,
                [
                    'hostname' => $config['host'] ?? 'localhost',
                    'port' => $config['port'] ?? 6379,
                    'database' => $config['database'] ?? 0
                ]
            );
        } catch (Exception $e) {
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

            return $this->formatResult(
                'failed',
                'Redis connection failed: ' . $e->getMessage(),
                $time,
                ['error' => $e->getMessage()]
            );
        }
    }
}
