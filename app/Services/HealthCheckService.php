<?php

namespace App\Services;

use App\HealthChecks\DatabaseHealthCheck;
use App\HealthChecks\RedisHealthCheck;
use App\HealthChecks\QueueHealthCheck;
use App\HealthChecks\FileExistenceHealthCheck;
use App\HealthChecks\DiskSpaceHealthCheck;
use App\HealthChecks\MemoryUsageHealthCheck;
use App\HealthChecks\CacheHealthCheck;
use App\HealthChecks\LogPermissionsHealthCheck;
use App\HealthChecks\MailHealthCheck;
use App\HealthChecks\EnvironmentHealthCheck;
use App\HealthChecks\HttpHealthCheck;
use App\HealthChecks\QueueFailedJobsHealthCheck;
use App\HealthChecks\DatabasePerformanceHealthCheck;

class HealthCheckService
{
    public function runAllChecks(): array
    {
        $healthChecks = [
            new DatabaseHealthCheck('Database', 'Checks database connection'),
            new DatabasePerformanceHealthCheck('Database Performance', 'Checks database query performance'),
            new RedisHealthCheck('Redis', 'Checks Redis connection'),
            new QueueHealthCheck('Queue', 'Checks Queue pending jobs'),
            new QueueFailedJobsHealthCheck('Queue Failed Jobs', 'Checks failed job count'),
            new CacheHealthCheck('Cache', 'Checks cache read/write operations'),
            new FileExistenceHealthCheck('File Existence', 'Checks if specified files exist'),
            new LogPermissionsHealthCheck('Log Permissions', 'Checks log directory write permissions'),
            new DiskSpaceHealthCheck('Disk Space', 'Checks available disk space'),
            new MemoryUsageHealthCheck('Memory Usage', 'Checks PHP memory consumption'),
            new EnvironmentHealthCheck('Environment', 'Checks required environment variables'),
            new MailHealthCheck('Mail', 'Checks mail configuration'),
            new HttpHealthCheck('HTTP', 'Checks application HTTP accessibility'),
        ];

        $results = [];
        $overallStatus = 'ok';

        foreach ($healthChecks as $check) {
            $result = $check->check();
            $results[] = $result;

            if ($result['status'] === 'failed') {
                $overallStatus = 'failed';
            } elseif ($result['status'] === 'warning' && $overallStatus === 'ok') {
                $overallStatus = 'warning';
            }
        }

        return [
            'status' => $overallStatus,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'checks' => $results
        ];
    }
}
