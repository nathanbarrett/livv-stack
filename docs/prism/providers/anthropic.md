# Anthropic

## Configuration

```php
'anthropic' => [
    'api_key' => env('ANTHROPIC_API_KEY', ''),
    'version' => env('ANTHROPIC_API_VERSION', '2023-06-01'),
    'default_thinking_budget' => env('ANTHROPIC_DEFAULT_THINKING_BUDGET', 1024),
    // Include beta strings as a comma separated list.
    'anthropic_beta' => env('ANTHROPIC_BETA', null),
]
```

## Prompt Caching

Anthropic's prompt caching feature allows you to drastically reduce latency and your API bill when repeatedly re-using blocks of content within five minutes or one hour of each other.

Supported caching targets:
- System Messages (text only)
- User Messages (Text, Image and PDF)
- Assistant Messages (text only)
- Tools

```php
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Tool;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Messages\SystemMessage;

Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022')
    ->withSystemPrompt(
        (new SystemMessage('I am a long re-usable system message.'))
            ->withProviderOptions(['cacheType' => 'ephemeral', 'cacheTtl' => '1h'])
    )
    ->withMessages([
        (new UserMessage('I am a long re-usable user message.'))
            ->withProviderOptions(['cacheType' => 'ephemeral'])
    ])
    ->withTools([
        Tool::as('cache me')
            ->withProviderOptions(['cacheType' => 'ephemeral'])
    ])
    ->asText();
```

You can also use the `AnthropicCacheType` Enum:

```php
use Prism\Prism\Providers\Anthropic\Enums\AnthropicCacheType;

(new UserMessage('I am a long re-usable user message.'))
    ->withProviderOptions(['cacheType' => AnthropicCacheType::ephemeral])
```

**Important:**
- System messages must use `withSystemPrompt()` or `withSystemPrompts()`
- User and Assistant messages must use `withMessages()`
- Anthropic supports two TTL options: `5m` (default) or `1h`

### Tool Result Caching

```php
$response = Prism::text()
    ->using('anthropic', 'claude-3-5-sonnet-20241022')
    ->withMaxSteps(30)
    ->withTools([new WeatherTool()])
    ->withProviderOptions([
        'tool_result_cache_type' => 'ephemeral'
    ])
    ->withPrompt('Check the weather in New York, London, Tokyo, Paris, and Sydney')
    ->asText();
```

## Extended Thinking

Claude Sonnet 3.7 supports an optional extended thinking mode for reasoning before returning an answer.

### Enabling Extended Thinking

```php
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

Prism::text()
    ->using('anthropic', 'claude-3-7-sonnet-latest')
    ->withPrompt('What is the meaning of life?')
    ->withProviderOptions(['thinking' => ['enabled' => true]])
    ->asText();
```

### Setting Budget

```php
Prism::text()
    ->using('anthropic', 'claude-3-7-sonnet-latest')
    ->withPrompt('What is the meaning of life?')
    ->withProviderOptions([
        'thinking' => [
            'enabled' => true,
            'budgetTokens' => 2048
        ]
    ]);
```

### Inspecting the Thinking Block

```php
$response = Prism::text()
    ->using('anthropic', 'claude-3-7-sonnet-latest')
    ->withPrompt('What is the meaning of life?')
    ->withProviderOptions(['thinking' => ['enabled' => true]])
    ->asText();

$response->additionalContent['thinking'];
```

### Extended Output Mode

Claude Sonnet 3.7 brings extended output mode which increases the output limit to 128k tokens. Enable by adding `output-128k-2025-02-19` to your `anthropic_beta` config.

## Streaming

```php
// Stream events
$stream = Prism::text()
    ->using('anthropic', 'claude-3-7-sonnet-latest')
    ->withPrompt('Write a story')
    ->asStream();

// Server-Sent Events
return Prism::text()
    ->using('anthropic', 'claude-3-7-sonnet-latest')
    ->withPrompt(request('message'))
    ->asEventStreamResponse();
```

### Streaming with Extended Thinking

```php
use Prism\Prism\Enums\StreamEventType;

foreach ($stream as $event) {
    match ($event->type()) {
        StreamEventType::ThinkingDelta => echo "[Thinking] " . $event->delta,
        StreamEventType::TextDelta => echo $event->delta,
        default => null,
    };
}
```

## Documents

Anthropic supports PDF, text and markdown documents. See the [Documents](../sections/input-documents.md) section.

### Custom Content Documents

For use with citations using your own chunking strategy:

```php
use Prism\Prism\ValueObjects\Media\Document;

Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022')
    ->withMessages([
        new UserMessage(
            content: "Is the grass green and the sky blue?",
            additionalContent: [
                Document::fromChunks(["The grass is green.", "Flamingos are pink.", "The sky is blue."])
            ]
        )
    ])
    ->asText();
```

## Citations

Enable citations using `withProviderOptions`:

```php
$response = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022')
    ->withMessages([
        new UserMessage(
            content: "Is the grass green and the sky blue?",
            additionalContent: [
                Document::fromChunks(
                    chunks: ["The grass is green.", "Flamingos are pink.", "The sky is blue."],
                    title: 'The colours of nature',
                    context: 'The go-to textbook on the colours found in nature!'
                )
            ]
        )
    ])
    ->withProviderOptions(['citations' => true])
    ->asText();
```

Access citations via `$response->additionalContent['citations']`.

## Code Execution

Anthropic offers built-in code execution capabilities. Enable the beta feature first:

```php
// In config/prism.php
'anthropic' => [
    'anthropic_beta' => 'code-execution-2025-05-22',
],
```

Then use:

```php
use Prism\Prism\ValueObjects\ProviderTool;

Prism::text()
    ->using('anthropic', 'claude-3-5-haiku-latest')
    ->withPrompt('Solve the equation 3x + 10 = 14.')
    ->withProviderTools([new ProviderTool(type: 'code_execution_20250522', name: 'code_execution')])
    ->asText();
```

## Structured Output

Prism supports three approaches for structured output with Anthropic:

### Native Structured Outputs (Claude Sonnet 4.5+)

Enable in configuration:

```php
'anthropic' => [
    'anthropic_beta' => env('ANTHROPIC_BETA', 'structured-outputs-2025-11-13'),
]
```

```php
$response = Prism::structured()
    ->withSchema(new ObjectSchema(
        'weather_report',
        'Weather forecast with recommendations',
        [
            new StringSchema('forecast', 'The weather forecast'),
            new StringSchema('recommendation', 'Clothing recommendation')
        ],
        ['forecast', 'recommendation']
    ))
    ->using(Provider::Anthropic, 'claude-sonnet-4-5-20250929')
    ->withPrompt('What\'s the weather like and what should I wear?')
    ->asStructured();
```

### Tool Calling Mode

For more reliable structured output on older models:

```php
$response = Prism::structured()
    ->withSchema($schema)
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-latest')
    ->withPrompt('What\'s the weather like?')
    ->withProviderOptions(['use_tool_calling' => true])
    ->asStructured();
```

### Combining Tools with Structured Output

```php
$response = Prism::structured()
    ->using('anthropic', 'claude-3-5-sonnet-latest')
    ->withSchema($schema)
    ->withTools([$weatherTool])
    ->withMaxSteps(3)
    ->withProviderOptions(['use_tool_calling' => true])
    ->withPrompt('What is the weather in San Francisco?')
    ->asStructured();
```

## Strict Tool Use

With the `structured-outputs-2025-11-13` beta:

```php
$weatherTool = Tool::as('get_weather')
    ->for('Get current weather for a location')
    ->withStringParameter('location', 'The city and state')
    ->withProviderOptions(['strict' => true])
    ->using(fn (string $location): string => "Weather in {$location}: 72°F, sunny");
```

## Considerations

### Message Order

Anthropic is strict about message order: `UserMessage` -> `AssistantMessage` -> `UserMessage`

### Limitations

- **Messages**: SystemMessages are filtered out and moved to the system property
- **Images**: Does not support `Image::fromURL`
