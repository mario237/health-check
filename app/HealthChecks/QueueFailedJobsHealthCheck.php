<?php

namespace App\HealthChecks;

use Exception;
use Illuminate\Support\Facades\DB;

class QueueFailedJobsHealthCheck extends BaseHealthCheck
{
    public function check(): array
    {
        $start = microtime(true);

        try {
            $failedJobs = DB::table('failed_jobs')->count();
            $recentFailed = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subHour())
                ->count();

            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

            $threshold = 10; // Alert if more than 10 failed jobs in last hour
            $status = $recentFailed > $threshold ? 'warning' : 'ok';
            $message = $status === 'warning'
                ? "High number of recent failed jobs: $recentFailed in last hour"
                : "Failed jobs are under control: $recentFailed in last hour";

            return $this->formatResult(
                $status,
                $message,
                $time,
                [
                    'total_failed_jobs' => $failedJobs,
                    'failed_last_hour' => $recentFailed,
                    'threshold' => $threshold
                ]
            );
        } catch (Exception $e) {
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';
            return $this->formatResult('failed', 'Failed jobs check failed: ' . $e->getMessage(), $time, ['error' => $e->getMessage()]);
        }
    }
}
