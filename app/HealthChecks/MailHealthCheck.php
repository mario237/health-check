<?php

namespace App\HealthChecks;

use Exception;
use RuntimeException;

class MailHealthCheck extends BaseHealthCheck
{
    public function check(): array
    {
        $start = microtime(true);

        try {
            $mailer = config('mail.default');
            $config = config("mail.mailers.$mailer");

            // For SMTP, try to connect
            if ($mailer === 'smtp') {
                $host = $config['host'];
                $port = $config['port'];
                $timeout = 5;

                $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
                if (!$connection) {
                    throw new RuntimeException("Cannot connect to SMTP server $host:$port - $errstr");
                }
                fclose($connection);
            }

            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

            return $this->formatResult(
                'ok',
                'Mail configuration is valid',
                $time,
                [
                    'driver' => $mailer,
                    'host' => $config['host'] ?? 'N/A',
                    'port' => $config['port'] ?? 'N/A',
                    'from_address' => config('mail.from.address'),
                    'from_name' => config('mail.from.name')
                ]
            );
        } catch (Exception $e) {
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';
            return $this->formatResult('failed', 'Mail check failed: ' . $e->getMessage(), $time, ['error' => $e->getMessage()]);
        }
    }
}
