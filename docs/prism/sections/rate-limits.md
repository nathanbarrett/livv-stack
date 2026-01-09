# Rate Limits

Many AI providers implement rate limits to manage API usage. Prism provides tools to help you monitor and respond to these limits.

See the [provider support table](../INDEX.md) and [error handling](error-handling.md) for provider-specific support details.

## Rate Limit Information

When available, Prism exposes rate limit information through the `ProviderRateLimit` value object:

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

$response = Prism::text()
    ->using(Provider::OpenAI, 'gpt-4o')
    ->withPrompt('Hello!')
    ->asText();

foreach ($response->rateLimits as $rateLimit) {
    echo "Limit: {$rateLimit->name}\n";
    echo "Limit: {$rateLimit->limit}\n";
    echo "Remaining: {$rateLimit->remaining}\n";
    echo "Resets at: {$rateLimit->resetsAt?->format('Y-m-d H:i:s')}\n";
}
```

### ProviderRateLimit Properties

| Property | Type | Description |
|----------|------|-------------|
| `name` | `string` | The name of the rate limit (e.g., "requests", "tokens") |
| `limit` | `int` | The maximum allowed requests/tokens in the period |
| `remaining` | `int` | How many requests/tokens you have left |
| `resetsAt` | `?CarbonImmutable` | When the rate limit resets (if provided) |

## Handling Rate Limit Errors

When you hit a rate limit, Prism throws a `PrismRateLimitedException`:

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismRateLimitedException;

try {
    $response = Prism::text()
        ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022')
        ->withPrompt('Hello!')
        ->asText();
} catch (PrismRateLimitedException $e) {
    // Access rate limit information
    foreach ($e->rateLimits as $limit) {
        Log::warning("Rate limit hit: {$limit->name}", [
            'limit' => $limit->limit,
            'remaining' => $limit->remaining,
            'resets_at' => $limit->resetsAt?->toDateTimeString(),
        ]);
    }

    // Check if retry-after header was provided
    if ($e->retryAfter !== null) {
        Log::info("Retry after {$e->retryAfter} seconds");
    }
}
```

### Exception Properties

| Property | Type | Description |
|----------|------|-------------|
| `rateLimits` | `array<ProviderRateLimit>` | Current rate limit status |
| `retryAfter` | `?int` | Seconds to wait before retrying (if provided) |

## Dynamic Rate Limiting

Use rate limit information from successful requests to dynamically manage your application's behavior:

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

$response = Prism::text()
    ->using(Provider::OpenAI, 'gpt-4o')
    ->withPrompt('Analyze this data...')
    ->asText();

// Find the requests rate limit
$requestsLimit = collect($response->rateLimits)
    ->firstWhere('name', 'requests');

if ($requestsLimit && $requestsLimit->remaining < 10) {
    // Slow down or queue subsequent requests
    Log::warning('Approaching rate limit', [
        'remaining' => $requestsLimit->remaining,
        'resets_at' => $requestsLimit->resetsAt,
    ]);
}
```

## Integration with Laravel Rate Limiting

You can integrate Prism's rate limit information with Laravel's built-in rate limiting:

```php
use Illuminate\Support\Facades\RateLimiter;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

$response = Prism::text()
    ->using(Provider::OpenAI, 'gpt-4o')
    ->withPrompt('Hello!')
    ->asText();

// Update Laravel rate limiter based on provider response
foreach ($response->rateLimits as $limit) {
    RateLimiter::hit(
        key: "prism:{$limit->name}",
        decaySeconds: $limit->resetsAt?->diffInSeconds(now()) ?? 60
    );
}
```

## Job Middleware for Rate Limiting

Create middleware to handle rate limits in queued jobs:

```php
namespace App\Jobs\Middleware;

use Closure;
use Prism\Prism\Exceptions\PrismRateLimitedException;

class HandlePrismRateLimits
{
    public function handle(object $job, Closure $next): void
    {
        try {
            $next($job);
        } catch (PrismRateLimitedException $e) {
            // Release the job back to the queue with a delay
            $delay = $e->retryAfter ?? 60;
            $job->release($delay);
        }
    }
}
```

Use the middleware in your job:

```php
namespace App\Jobs;

use App\Jobs\Middleware\HandlePrismRateLimits;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWithAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function middleware(): array
    {
        return [new HandlePrismRateLimits()];
    }

    public function handle(): void
    {
        // Your AI processing logic
    }
}
```

## Best Practices

1. **Monitor proactively**: Check `remaining` counts on successful requests, not just when errors occur
2. **Implement backoff**: Use exponential backoff when retrying after rate limits
3. **Queue heavy workloads**: Use Laravel queues with rate limit middleware for batch processing
4. **Cache responses**: Where appropriate, cache AI responses to reduce API calls
5. **Use webhooks**: For high-volume applications, consider provider webhooks or polling strategies
