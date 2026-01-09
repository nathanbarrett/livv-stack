# Custom Providers

Want to add support for a new AI provider in Prism? This guide walks you through creating and registering your own custom provider implementation.

## Building Your Provider

All providers must extend the `Prism\Prism\Providers\Provider` abstract class. This base class provides default implementations for all required methods, throwing exceptions for unsupported actions.

You only need to override the methods for the features you want to support:

- `text()` - For text generation
- `structured()` - For structured output generation
- `embeddings()` - For creating embeddings
- `images()` - For image generation
- `stream()` - For streaming text responses

```php
namespace App\Prism\Providers;

use Prism\Prism\Providers\Provider;
use Prism\Prism\Text\Request as TextRequest;
use Prism\Prism\Text\Response as TextResponse;

class MyCustomProvider extends Provider
{
    public function __construct(
        protected string $apiKey,
    ) {}

    public function text(TextRequest $request): TextResponse
    {
        // Your text generation logic here
        // Make API calls, process the response, and return a TextResponse
    }

    // Only override the methods you need!
}
```

## Registration Process

Register your provider with Prism in a service provider:

```php
namespace App\Providers;

use App\Prism\Providers\MyCustomProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app['prism-manager']->extend('my-custom-provider', function ($app, $config) {
            return new MyCustomProvider(
                apiKey: $config['api_key'] ?? '',
            );
        });
    }
}
```

Add your provider configuration to `config/prism.php`:

```php
return [
    'providers' => [
        // ... other providers ...
        'my-custom-provider' => [
            'api_key' => env('MY_CUSTOM_PROVIDER_API_KEY'),
        ],
    ],
];
```

Use your custom provider:

```php
use Prism\Prism\Facades\Prism;

$response = Prism::text()
    ->using('my-custom-provider', 'model-name')
    ->withPrompt('Hello, custom AI!')
    ->asText();
```

## Custom Error Handling

Your provider inherits a default `handleRequestException` method. Override it to handle provider-specific errors:

```php
use Illuminate\Http\Client\RequestException;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Exceptions\PrismRateLimitedException;

class MyCustomProvider extends Provider
{
    // ... other methods ...

    public function handleRequestException(string $model, RequestException $e): never
    {
        // Handle provider-specific error codes
        match ($e->response->getStatusCode()) {
            429 => throw PrismRateLimitedException::make(
                rateLimits: $this->processRateLimits($e->response),
                retryAfter: $e->response->header('retry-after') === ''
                    ? null
                    : (int) $e->response->header('retry-after'),
            ),
            default => parent::handleRequestException($model, $e),
        };
    }
}
```

The method must throw an exception (return type `never`). If you don't handle a specific status code, call the parent method to maintain default error handling.

## Best Practices

- **Start small**: Begin by implementing just the methods you need. You don't have to support every feature right away.
- **Handle errors gracefully**: Leverage the inherited error handling or override `handleRequestException()` for provider-specific errors.
- **Test thoroughly**: Make sure to test your provider with various inputs and edge cases.
- **Document your models**: Let users know which models your provider supports and any special parameters they can use.

> **TIP:** Looking at existing provider implementations in Prism's source code can give you great insights into best practices and patterns to follow.
