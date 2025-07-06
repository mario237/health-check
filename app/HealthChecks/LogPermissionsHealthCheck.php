<?php

namespace App\HealthChecks;

use Exception;
use RuntimeException;

class LogPermissionsHealthCheck extends BaseHealthCheck
{
    public function check(): array
    {
        $start = microtime(true);

        try {
            $logPath = storage_path('logs');
            $testFile = $logPath . '/health_check_test.log';

            // Check if logs directory exists and is writable
            if (!is_dir($logPath)) {
                throw new RuntimeException('Logs directory does not exist');
            }

            if (!is_writable($logPath)) {
                throw new RuntimeException('Logs directory is not writable');
            }

            // Test writing to log file
            file_put_contents($testFile, 'Health check test - ' . date('Y-m-d H:i:s'));

            if (!file_exists($testFile)) {
                throw new RuntimeException('Could not create test log file');
            }

            // Clean up test file
            unlink($testFile);

            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

            return $this->formatResult(
                'ok',
                'Log directory is writable',
                $time,
                [
                    'logs_path' => $logPath,
                    'permissions' => substr(sprintf('%o', fileperms($logPath)), -4)
                ]
            );
        } catch (Exception $e) {
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';
            return $this->formatResult('failed', 'Log permissions check failed: ' . $e->getMessage(), $time, ['error' => $e->getMessage()]);
        }
    }
}
