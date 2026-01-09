# Provider Interoperability

When working with Prism, you might need to customize requests based on which provider you're using. Different providers have unique capabilities, configuration options, and requirements.

## Using the `whenProvider` Method

The `whenProvider` method lets you customize requests for specific providers while maintaining clean, readable code:

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

$response = Prism::text()
    ->using(Provider::OpenAI, 'gpt-4o')
    ->withPrompt('Who are you?')
    ->whenProvider(
        Provider::Anthropic,
        fn ($request) => $request
            ->withProviderOptions([
                'cacheType' => 'ephemeral',
            ])
    )
    ->asText();
```

In this example, the `withProviderOptions` settings will only be applied when using Anthropic's provider. If you're using OpenAI, these customizations are skipped.

## Key Benefits

- **Cleaner Code**: Keep provider-specific customizations encapsulated
- **Easy Provider Switching**: Swap between providers without rewriting configuration
- **Maintainable Applications**: Define provider-specific behaviors in one place

## Advanced Usage

Chain multiple `whenProvider` calls to handle different provider scenarios:

```php
$response = Prism::text()
    ->using(Provider::OpenAI, 'gpt-4o')
    ->withPrompt('Generate a creative story about robots.')
    ->whenProvider(
        Provider::Anthropic,
        fn ($request) => $request
            ->withMaxTokens(4000)
            ->withProviderOptions(['cacheType' => 'ephemeral'])
    )
    ->whenProvider(
        Provider::OpenAI,
        fn ($request) => $request
            ->withMaxTokens(2000)
            ->withProviderOptions(['response_format' => ['type' => 'text']])
    )
    ->asText();
```

## Using Invokable Classes

For complex provider-specific configurations, use invokable classes:

```php
class AnthropicConfigurator
{
    public function __invoke($request)
    {
        return $request
            ->withMaxTokens(4000)
            ->withProviderOptions([
                'cacheType' => 'ephemeral',
                'citations' => true,
            ]);
    }
}

$response = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-sonnet')
    ->withPrompt('Explain the theory of relativity.')
    ->whenProvider(Provider::Anthropic, new AnthropicConfigurator())
    ->asText();
```

This is especially helpful for complex or reusable provider configurations.

> **TIP:** The `whenProvider` method works with all request types including text, structured output, and embeddings requests.

## Best Practices

### Avoiding SystemMessages with Multiple Providers

When working with multiple providers, avoid using `SystemMessages` directly in your `withMessages` array. Instead, use the `withSystemPrompt` method for better provider interoperability:

```php
// Avoid this when switching between providers
$response = Prism::text()
    ->using(Provider::OpenAI, 'gpt-4o')
    ->withMessages([
        new SystemMessage('You are a helpful assistant.'),
        new UserMessage('Tell me about AI'),
    ])
    ->asText();

// Prefer this instead
$response = Prism::text()
    ->using(Provider::OpenAI, 'gpt-4o')
    ->withSystemPrompt('You are a helpful assistant.')
    ->withPrompt('Tell me about AI')
    ->asText();
```

This approach allows Prism to handle the provider-specific formatting of system messages, making your code more portable across different LLM providers.
