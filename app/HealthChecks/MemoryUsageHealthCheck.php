<?php

namespace App\HealthChecks;

use Exception;

class MemoryUsageHealthCheck extends BaseHealthCheck
{
    public function check(): array
    {
        $start = microtime(true);

        try {
            $memoryUsage = memory_get_usage(true);
            $memoryPeak = memory_get_peak_usage(true);
            $memoryLimit = $this->getMemoryLimit();

            $usageMB = round($memoryUsage / 1024 / 1024, 2);
            $peakMB = round($memoryPeak / 1024 / 1024, 2);
            $limitMB = $memoryLimit ? round($memoryLimit / 1024 / 1024, 2) : 'unlimited';

            $usagePercent = $memoryLimit ? round(($memoryUsage / $memoryLimit) * 100, 2) : 0;

            $threshold = 80; // Alert if memory usage > 80%
            $status = ($memoryLimit && $usagePercent > $threshold) ? 'warning' : 'ok';
            $message = $status === 'warning'
                ? "Memory usage is high: $usagePercent%"
                : "Memory usage is normal: {$usageMB}MB";

            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

            return $this->formatResult(
                $status,
                $message,
                $time,
                [
                    'current_usage_mb' => $usageMB,
                    'peak_usage_mb' => $peakMB,
                    'memory_limit_mb' => $limitMB,
                    'usage_percent' => $usagePercent,
                    'threshold_percent' => $threshold
                ]
            );
        } catch (Exception $e) {
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';
            return $this->formatResult('failed', 'Memory check failed: ' . $e->getMessage(), $time, ['error' => $e->getMessage()]);
        }
    }

    private function getMemoryLimit(): float|int|null
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1') {
            return null; // unlimited
        }

        $value = (int) $limit;
        $unit = strtolower(substr($limit, -1));

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
