# Laravel Health Check System

A comprehensive health monitoring system for Laravel applications that provides detailed status information about your application's critical components.

## Features

- 🔍 **13 Comprehensive Health Checks** covering all critical application components
- 🚨 **Three Status Levels**: `ok`, `warning`, `failed`
- ⚡ **Performance Monitoring** with response time tracking
- 📊 **Detailed Metrics** for each check
- 🎯 **Production Ready** with proper error handling
- 🔧 **Easily Extensible** to add custom checks

## Installation

### 1. Create Directory Structure

```bash
mkdir -p app/HealthChecks
mkdir -p app/Services
```

### 2. Create Base Health Check Class

Create `app/HealthChecks/BaseHealthCheck.php`:

```php
<?php

namespace App\HealthChecks;

abstract class BaseHealthCheck
{
    protected $name;
    protected $description;

    public function __construct($name = null, $description = null)
    {
        $this->name = $name;
        $this->description = $description;
    }

    abstract public function check(): array;

    protected function formatResult($status, $message, $time, $details = [])
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
```

### 3. Create Individual Health Check Classes

Create the following files in `app/HealthChecks/`:

- `DatabaseHealthCheck.php`
- `DatabasePerformanceHealthCheck.php`
- `RedisHealthCheck.php`
- `QueueHealthCheck.php`
- `QueueFailedJobsHealthCheck.php`
- `CacheHealthCheck.php`
- `FileExistenceHealthCheck.php`
- `LogPermissionsHealthCheck.php`
- `DiskSpaceHealthCheck.php`
- `MemoryUsageHealthCheck.php`
- `EnvironmentHealthCheck.php`
- `MailHealthCheck.php`
- `HttpHealthCheck.php`

*See the complete implementations in the provided code artifact.*

### 4. Create Health Check Service

Create `app/Services/HealthCheckService.php`:

```php
<?php

namespace App\Services;

use App\HealthChecks\DatabaseHealthCheck;
// ... import all other health checks

class HealthCheckService
{
    public function runAllChecks(): array
    {
        $healthChecks = [
            new DatabaseHealthCheck('Database', 'Checks database connection'),
            new DatabasePerformanceHealthCheck('Database Performance', 'Checks database query performance'),
            // ... add all other health checks
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
```

### 5. Create Health Check Controller

Create `app/Http/Controllers/HealthController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    protected $healthCheckService;

    public function __construct(HealthCheckService $healthCheckService)
    {
        $this->healthCheckService = $healthCheckService;
    }

    public function status(): JsonResponse
    {
        $results = $this->healthCheckService->runAllChecks();
        
        return response()->json($results);
    }
}
```

### 6. Add Route

Add to your `routes/web.php` or `routes/api.php`:

```php
use App\Http\Controllers\HealthController;

Route::get('health/status', [HealthController::class, 'status']);
```

## Configuration

### Environment Variables

Add the following optional environment variables to your `.env` file:

```env
# Redis Configuration (if using Redis)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue Configuration (if using Redis queues)
QUEUE_CONNECTION=redis

# Mail Configuration (for SMTP checks)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null

# Custom thresholds (optional)
HEALTH_CHECK_DISK_THRESHOLD=90
HEALTH_CHECK_MEMORY_THRESHOLD=80
HEALTH_CHECK_FAILED_JOBS_THRESHOLD=10
```

### Customizing File Checks

To customize which files are checked for existence, modify the `$files` array in `FileExistenceHealthCheck.php`:

```php
protected $files = [
    '.env',
    'storage/logs/laravel.log',
    'config/app.php',
    // Add your critical files here
];
```

### Customizing Environment Variable Checks

Modify the `$requiredEnvVars` array in `EnvironmentHealthCheck.php`:

```php
$requiredEnvVars = [
    'APP_KEY',
    'APP_ENV',
    'DB_CONNECTION',
    'DB_HOST',
    'DB_DATABASE',
    // Add your required variables here
];
```

## Usage

### Basic Usage

Access the health check endpoint:

```bash
curl https://yourapp.com/health/status
```

### Example Response

```json
{
  "status": "ok",
  "timestamp": "2025-07-06 08:26:12",
  "checks": [
    {
      "name": "Database",
      "description": "Checks database connection",
      "status": "ok",
      "message": "Database connection is working",
      "time": "3.1ms",
      "details": []
    },
    {
      "name": "Redis",
      "description": "Checks Redis connection",
      "status": "ok",
      "message": "Redis connection is working",
      "time": "20.86ms",
      "details": {
        "hostname": "127.0.0.1",
        "port": "6379",
        "database": "0"
      }
    },
    {
      "name": "Disk Space",
      "description": "Checks available disk space",
      "status": "warning",
      "message": "Disk usage is high: 92%",
      "time": "1.23ms",
      "details": {
        "free_space_gb": 5.2,
        "total_space_gb": 64.0,
        "used_space_gb": 58.8,
        "usage_percent": 92,
        "threshold_percent": 90
      }
    }
  ]
}
```

### Status Codes

- **`ok`**: All systems are functioning normally
- **`warning`**: Some issues detected but application is still functional
- **`failed`**: Critical issues that may affect application functionality

## Health Checks Included

| Check Name | Description | What It Monitors |
|------------|-------------|------------------|
| Database | Database connectivity | Connection to primary database |
| Database Performance | Query performance | Database response times |
| Redis | Redis connectivity | Redis server connection and R/W operations |
| Queue | Queue status | Pending jobs across all priority queues |
| Queue Failed Jobs | Failed job monitoring | Number of failed jobs in last hour |
| Cache | Cache operations | Cache read/write functionality |
| File Existence | Critical files | Presence of important application files |
| Log Permissions | Log directory | Write permissions for log storage |
| Disk Space | Storage usage | Available disk space on storage drive |
| Memory Usage | PHP memory | Current and peak memory consumption |
| Environment | Configuration | Required environment variables |
| Mail | Email configuration | SMTP server connectivity |
| HTTP | Application access | HTTP accessibility of the application |

## Monitoring Integration

### Uptime Monitoring

Use this endpoint with uptime monitoring services like:
- **Pingdom**: Monitor the `/health/status` endpoint
- **UptimeRobot**: Set up HTTP keyword monitoring for `"status":"ok"`
- **StatusCake**: Monitor HTTP response and JSON content
- **New Relic**: Set up synthetic monitoring

### Log Monitoring

The health checks automatically log failures. Monitor your Laravel logs for health check errors:

```bash
tail -f storage/logs/laravel.log | grep "health"
```

### Alerting

Set up alerts based on the response:
- **Status `failed`**: Immediate alert
- **Status `warning`**: Warning notification
- **Response time > 5 seconds**: Performance alert

## Customization

### Adding Custom Health Checks

1. Create a new class extending `BaseHealthCheck`:

```php
<?php

namespace App\HealthChecks;

class CustomHealthCheck extends BaseHealthCheck
{
    public function check(): array
    {
        $start = microtime(true);
        
        try {
            // Your custom check logic here
            $result = $this->performCustomCheck();
            
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';
            
            return $this->formatResult(
                'ok',
                'Custom check passed',
                $time,
                ['custom_data' => $result]
            );
        } catch (\Exception $e) {
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';
            return $this->formatResult('failed', 'Custom check failed: ' . $e->getMessage(), $time, ['error' => $e->getMessage()]);
        }
    }
    
    private function performCustomCheck()
    {
        // Implement your check logic
        return 'success';
    }
}
```

2. Add it to the `HealthCheckService`:

```php
$healthChecks = [
    // ... existing checks
    new CustomHealthCheck('Custom Check', 'Description of custom check'),
];
```

### Excluding Health Checks

Comment out or remove checks you don't need from the `HealthCheckService`:

```php
$healthChecks = [
    new DatabaseHealthCheck('Database', 'Checks database connection'),
    // new RedisHealthCheck('Redis', 'Checks Redis connection'), // Disabled
    new QueueHealthCheck('Queue', 'Checks Queue pending jobs'),
    // ... other checks
];
```

## Troubleshooting

### Common Issues

1. **Redis Connection Failed**: Ensure Redis is running and accessible
2. **Queue Checks Failing**: Verify queue configuration and Redis connection
3. **File Permission Errors**: Check storage directory permissions
4. **Memory/Disk Warnings**: Monitor and optimize resource usage



## Security Considerations

- **Authentication**: Consider adding authentication to the health endpoint in production
- **Rate Limiting**: Implement rate limiting to prevent abuse
- **Sensitive Data**: Avoid exposing sensitive information in health check details

### Example Middleware for Authentication

```php
Route::get('health/status', [HealthController::class, 'status']);
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Add your health check with tests
4. Submit a pull request

## License

This health check system is open-sourced software licensed under the [MIT license](LICENSE).

## Support

If you encounter any issues or have questions:
1. Check the troubleshooting section
2. Review Laravel logs for detailed error messages
3. Open an issue on GitHub with full error details and environment information
