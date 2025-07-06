<?php

namespace App\HealthChecks;

use Exception;
use Illuminate\Support\Facades\DB;

class DatabasePerformanceHealthCheck extends BaseHealthCheck
{
    public function check(): array
    {
        $start = microtime(true);

        try {
            // Test a simple query performance
            $queryStart = microtime(true);
            DB::select('SELECT 1');
            $queryTime = round((microtime(true) - $queryStart) * 1000, 2);

            // Check active connections (MySQL)
            $connections = $this->getActiveConnections();

            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

            $threshold = 100; // Alert if query takes more than 100ms
            $status = $queryTime > $threshold ? 'warning' : 'ok';
            $message = $status === 'warning'
                ? "Database query is slow: {$queryTime}ms"
                : "Database performance is good: {$queryTime}ms";

            return $this->formatResult(
                $status,
                $message,
                $time,
                [
                    'query_time_ms' => $queryTime,
                    'active_connections' => $connections,
                    'database_driver' => config('database.default'),
                    'threshold_ms' => $threshold
                ]
            );
        } catch (Exception $e) {
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';
            return $this->formatResult('failed', 'Database performance check failed: ' . $e->getMessage(), $time, ['error' => $e->getMessage()]);
        }
    }

    private function getActiveConnections(): ?string
    {
        try {
            $driver = config('database.default');
            $connection = config("database.connections.$driver.driver");

            if ($connection === 'mysql') {
                $result = DB::select('SHOW STATUS LIKE "Threads_connected"');
                return $result[0]->Value ?? 'unknown';
            }

            return 'unknown';
        } catch (Exception $e) {
            return 'error: ' . $e->getMessage();
        }
    }
}
