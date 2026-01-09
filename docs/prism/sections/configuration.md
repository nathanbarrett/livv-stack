# Configuration

Prism's flexible configuration allows you to easily set up and switch between different AI providers.

## Configuration File

After installation, you'll find the Prism configuration file at `config/prism.php`. If you haven't published it yet:

```bash
php artisan vendor:publish --tag=prism-config
```

The configuration file structure:

```php
return [
    'prism_server' => [
        'enabled' => env('PRISM_SERVER_ENABLED', false),
    ],
    'providers' => [
        // Provider configurations here
    ],
];
```

## Provider Configuration

Prism uses a straightforward provider configuration system. Each provider has its own section where you can specify:

- API credentials
- Base URLs (useful for self-hosted instances or custom endpoints)
- Other provider-specific settings

General template for provider configuration:

```php
'providers' => [
    'provider-name' => [
        'api_key' => env('PROVIDER_API_KEY', ''),
        'url' => env('PROVIDER_URL', 'https://api.provider.com'),
        // Other provider-specific settings
    ],
],
```

## Environment Variables

Prism follows Laravel's environment configuration best practices:

1. Each provider's configuration pulls values from environment variables
2. Default values are provided as fallbacks
3. Environment variables follow a predictable naming pattern:
   - API keys: `PROVIDER_API_KEY`
   - URLs: `PROVIDER_URL`
   - Other settings: `PROVIDER_SETTING_NAME`

Example `.env` configuration:

```shell
# Prism Server Configuration
PRISM_SERVER_ENABLED=true

# Provider Configuration
OPENAI_API_KEY=your-api-key-here
ANTHROPIC_API_KEY=your-anthropic-key-here
```

> **NOTE:** Always refer to your chosen provider's documentation pages for the most up-to-date configuration options and requirements.

## Overriding Config in Your Code

You can override config in your code in two ways:

### Via the third parameter of `using()`

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

$response = Prism::text()
    ->using(Provider::OpenAI, 'gpt-4o', [
        'url' => 'new-base-url'
    ])
    ->withPrompt('Explain quantum computing.')
    ->asText();
```

### Via `usingProviderConfig()`

```php
$response = Prism::text()
    ->using(Provider::OpenAI, 'gpt-4o')
    ->usingProviderConfig([
        'url' => 'new-base-url'
    ])
    ->withPrompt('Explain quantum computing.')
    ->asText();
```

> **NOTE:** Using `usingProviderConfig()` will re-resolve the provider.
