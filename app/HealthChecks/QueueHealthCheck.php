<?php

namespace App\HealthChecks;

use Exception;
use Illuminate\Support\Facades\Redis;

class QueueHealthCheck extends BaseHealthCheck
{
    public function check(): array
    {
        $start = microtime(true);

        try {
            $redis = Redis::connection();

            // Get queue statistics
            $highPriority = $this->getQueueStats($redis, 'high_priority');
            $mediumPriority = $this->getQueueStats($redis, 'medium_priority');
            $lowPriority = $this->getQueueStats($redis, 'low_priority');
            $defaultQueue = $this->getQueueStats($redis, 'queue_new_portal');

            $totalJobs = $defaultQueue['total'] + $highPriority['total'] + $mediumPriority['total'] + $lowPriority['total'];

            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

            return $this->formatResult(
                'ok',
                "Queue is healthy with $totalJobs pending jobs",
                $time,
                [
                    'waiting' => $defaultQueue['waiting'],
                    'delayed' => $defaultQueue['delayed'],
                    'reserved' => $defaultQueue['reserved'],
                    'total' => $defaultQueue['total'],
                    'channel' => 'queue_new_portal',
                    'high_priority' => [
                        'waiting' => $highPriority['waiting'],
                        'delayed' => $highPriority['delayed'],
                        'reserved' => $highPriority['reserved'],
                        'total' => $highPriority['total'],
                        'channel' => 0
                    ],
                    'medium_priority' => [
                        'waiting' => $mediumPriority['waiting'],
                        'delayed' => $mediumPriority['delayed'],
                        'reserved' => $mediumPriority['reserved'],
                        'total' => $mediumPriority['total'],
                        'channel' => 0
                    ],
                    'low_priority' => [
                        'waiting' => $lowPriority['waiting'],
                        'delayed' => $lowPriority['delayed'],
                        'reserved' => $lowPriority['reserved'],
                        'total' => $lowPriority['total'],
                        'channel' => 0
                    ],
                    'total_all_queues' => $totalJobs
                ]
            );
        } catch (Exception $e) {
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';

            return $this->formatResult(
                'failed',
                'Queue check failed: ' . $e->getMessage(),
                $time,
                ['error' => $e->getMessage()]
            );
        }
    }

    private function getQueueStats($redis, $queueName): ?array
    {
        try {
            $waiting = $redis->llen("queues:$queueName") ?? 0;
            $delayed = $redis->zcard("queues:$queueName:delayed") ?? 0;
            $reserved = $redis->zcard("queues:$queueName:reserved") ?? 0;

            return [
                'waiting' => $waiting,
                'delayed' => $delayed,
                'reserved' => $reserved,
                'total' => $waiting + $delayed + $reserved
            ];
        } catch (Exception) {
            return [
                'waiting' => 0,
                'delayed' => 0,
                'reserved' => 0,
                'total' => 0
            ];
        }
    }
}
