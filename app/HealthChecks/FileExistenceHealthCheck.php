<?php

namespace App\HealthChecks;

class FileExistenceHealthCheck extends BaseHealthCheck
{
    protected array $files = [
        '.env',
        'storage/logs/laravel.log',
    ];

    public function check(): array
    {
        $start = microtime(true);

        $missingFiles = [];

        foreach ($this->files as $file) {
            if (!file_exists(base_path($file))) {
                $missingFiles[] = $file;
            }
        }

        $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

        if (empty($missingFiles)) {
            return $this->formatResult(
                'ok',
                'All specified files exist',
                $time,
                [
                    'files' => $this->files
                ]
            );
        }

        return $this->formatResult(
            'failed',
            'Missing files: ' . implode(', ', $missingFiles),
            $time,
            [
                'files' => $this->files,
                'missing' => $missingFiles
            ]
        );
    }
}
