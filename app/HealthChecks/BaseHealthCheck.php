<?php

namespace App\HealthChecks;

abstract class BaseHealthCheck
{
    protected string $name;
    protected string $description;

    public function __construct(?string $name = null, ?string $description = null)
    {
        $this->name = $name;
        $this->description = $description;
    }

    abstract public function check(): array;

    protected function formatResult($status, $message, $time, $details = []): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'status' => $status,
            'message' => $message,
            'time' => $time,
            'details' => $details
        ];
    }
}
