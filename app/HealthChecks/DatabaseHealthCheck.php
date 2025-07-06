<?php

namespace App\HealthChecks;

use Exception;
use Illuminate\Support\Facades\DB;

class DatabaseHealthCheck extends BaseHealthCheck
{
    public function check(): array
    {
        $start = microtime(true);

        try {
            DB::connection()->getPdo();
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

            return $this->formatResult(
                'ok',
                'Database connection is working',
                $time
            );
        } catch (Exception $e) {
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

            return $this->formatResult(
                'failed',
                'Database connection failed: ' . $e->getMessage(),
                $time,
                ['error' => $e->getMessage()]
            );
        }
    }
}
