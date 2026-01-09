# Error Handling

By default, Prism throws a `PrismException` for Prism errors, or a `PrismServerException` for Prism Server errors.

For production use cases, you may need to catch exceptions more granularly to provide useful error messages, implement failover, or add retry logic.

## Provider Agnostic Exceptions

- `PrismStructuredDecodingException` - When a provider returns invalid JSON for a structured request

## Exceptions Based on Provider Feedback

Prism supports three exceptions based on provider feedback:

- `PrismRateLimitedException` - When you hit a rate limit or quota (see [Rate Limits](rate-limits.md))
- `PrismProviderOverloadedException` - When the provider cannot fulfill your request due to capacity issues
- `PrismRequestTooLargeException` - When your request is too large

### Provider Support

As providers all handle errors differently, support is being rolled out incrementally:

| Provider | Rate Limited | Overloaded | Too Large |
|----------|--------------|------------|-----------|
| Amazon Bedrock | ✗ | ✗ | ✗ |
| Anthropic | ✓ | ✓ | ✓ |
| Azure OpenAI | ✗ | ✗ | ✗ |
| DeepSeek | ✗ | ✗ | ✗ |
| Gemini | ✗ | ✗ | ✗ |
| Groq | ✓ | ✗ | ✗ |
| Mistral | ✓ | ✗ | ✗ |
| Ollama | ✗ | ✗ | ✗ |
| OpenAI | ✓ | ✗ | ✗ |
| Voyage AI | ✗ | ✗ | ✗ |
| xAI | ✗ | ✗ | ✗ |

## Basic Error Handling

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Exceptions\PrismRateLimitedException;
use Prism\Prism\Exceptions\PrismProviderOverloadedException;
use Throwable;

try {
    $response = Prism::text()
        ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022')
        ->withPrompt('Generate text...')
        ->asText();
} catch (PrismRateLimitedException $e) {
    // Handle rate limiting - see Rate Limits documentation
    Log::warning('Rate limited:', ['limits' => $e->rateLimits]);
} catch (PrismProviderOverloadedException $e) {
    // Handle provider capacity issues - maybe retry later
    Log::warning('Provider overloaded');
} catch (PrismException $e) {
    // Handle other Prism-specific errors
    Log::error('Prism error:', ['error' => $e->getMessage()]);
} catch (Throwable $e) {
    // Handle any other errors
    Log::error('Generic error:', ['error' => $e->getMessage()]);
}
```

## Structured Output Error Handling

```php
use Prism\Prism\Exceptions\PrismStructuredDecodingException;

try {
    $response = Prism::structured()
        ->using(Provider::OpenAI, 'gpt-4o')
        ->withSchema($schema)
        ->withPrompt('Generate structured data')
        ->asStructured();
} catch (PrismStructuredDecodingException $e) {
    // The provider returned invalid JSON
    Log::error('Failed to decode structured response:', [
        'error' => $e->getMessage()
    ]);
}
```
