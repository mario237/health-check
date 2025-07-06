<?php

namespace App\HealthChecks;

use Exception;

class EnvironmentHealthCheck extends BaseHealthCheck
{
    public function check(): array
    {
        $start = microtime(true);

        try {
            $requiredEnvVars = [
                'APP_KEY',
                'APP_ENV',
                'DB_CONNECTION',
                'DB_HOST',
                'DB_DATABASE'
            ];

            $missing = [];
            $present = [];

            foreach ($requiredEnvVars as $var) {
                $value = env($var);
                if (empty($value)) {
                    $missing[] = $var;
                } else {
                    $present[] = $var;
                }
            }

            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

            if (!empty($missing)) {
                return $this->formatResult(
                    'failed',
                    'Missing required environment variables: ' . implode(', ', $missing),
                    $time,
                    [
                        'missing_variables' => $missing,
                        'present_variables' => $present,
                        'app_env' => app()->environment(),
                        'debug_mode' => config('app.debug')
                    ]
                );
            }

            return $this->formatResult(
                'ok',
                'All required environment variables are set',
                $time,
                [
                    'required_variables' => $requiredEnvVars,
                    'app_env' => app()->environment(),
                    'debug_mode' => config('app.debug'),
                    'timezone' => config('app.timezone')
                ]
            );
        } catch (Exception $e) {
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';
            return $this->formatResult('failed', 'Environment check failed: ' . $e->getMessage(), $time, ['error' => $e->getMessage()]);
        }
    }
}
