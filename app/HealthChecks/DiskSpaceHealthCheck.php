<?php

namespace App\HealthChecks;

use Exception;

class DiskSpaceHealthCheck extends BaseHealthCheck
{
    public function check(): array
    {
        $start = microtime(true);

        try {
            $storagePath = storage_path();
            $freeBytes = disk_free_space($storagePath);
            $totalBytes = disk_total_space($storagePath);
            $usedBytes = $totalBytes - $freeBytes;

            $freeGB = round($freeBytes / 1024 / 1024 / 1024, 2);
            $totalGB = round($totalBytes / 1024 / 1024 / 1024, 2);
            $usedGB = round($usedBytes / 1024 / 1024 / 1024, 2);
            $usagePercent = round(($usedBytes / $totalBytes) * 100, 2);

            $threshold = 90; // Alert if disk usage > 90%
            $status = $usagePercent > $threshold ? 'warning' : 'ok';
            $message = $usagePercent > $threshold
                ? "Disk usage is high: $usagePercent%"
                : "Disk space is healthy: $usagePercent% used";

            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

            return $this->formatResult(
                $status,
                $message,
                $time,
                [
                    'free_space_gb' => $freeGB,
                    'total_space_gb' => $totalGB,
                    'used_space_gb' => $usedGB,
                    'usage_percent' => $usagePercent,
                    'path' => $storagePath,
                    'threshold_percent' => $threshold
                ]
            );
        } catch (Exception $e) {
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';
            return $this->formatResult('failed', 'Disk space check failed: ' . $e->getMessage(), $time, ['error' => $e->getMessage()]);
        }
    }
}
