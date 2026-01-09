# Structured Output

Structured output lets you define exactly how you want your data formatted, making it perfect for building APIs, processing forms, or any time you need data in a specific shape.

## Quick Start

> **IMPORTANT:** When using OpenAI's structured output (especially strict mode), the root schema must be an `ObjectSchema`. Other schema types can only be used as properties within an ObjectSchema.

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

$schema = new ObjectSchema(
    name: 'movie_review',
    description: 'A structured movie review',
    properties: [
        new StringSchema('title', 'The movie title'),
        new StringSchema('rating', 'Rating out of 5 stars'),
        new StringSchema('summary', 'Brief review summary')
    ],
    requiredFields: ['title', 'rating', 'summary']
);

$response = Prism::structured()
    ->using(Provider::OpenAI, 'gpt-4o')
    ->withSchema($schema)
    ->withPrompt('Review the movie Inception')
    ->asStructured();

// Access your structured data
$review = $response->structured;
echo $review['title'];    // "Inception"
echo $review['rating'];   // "5 stars"
echo $review['summary'];  // "A mind-bending..."
```

> **TIP:** Check out the [Schemas](schemas.md) guide to learn about all available schema types.

## Understanding Output Modes

Different AI providers handle structured output in two main ways:

1. **Structured Mode**: Some providers support strict schema validation, ensuring responses perfectly match your defined structure.
2. **JSON Mode**: Other providers simply guarantee valid JSON output that approximately matches your schema.

## Provider-Specific Options

### OpenAI: Strict Mode

OpenAI supports a "strict mode" for tighter schema validation:

```php
$response = Prism::structured()
    ->withProviderOptions([
        'schema' => [
            'strict' => true
        ]
    ])
    // ... rest of your configuration
```

### Anthropic: Tool Calling Mode

Anthropic doesn't have native structured output, but Prism provides tool calling mode for more reliable JSON parsing:

```php
$response = Prism::structured()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-latest')
    ->withSchema($schema)
    ->withPrompt('天氣怎麼樣？應該穿什麼？') // Chinese text with potential quotes
    ->withProviderOptions(['use_tool_calling' => true])
    ->asStructured();
```

**When to use tool calling mode with Anthropic:**
- Working with non-English content that may contain quotes
- Complex JSON structures that might confuse prompt-based parsing
- When you need the most reliable structured output possible

> **NOTE:** Tool calling mode cannot be used with Anthropic's citations feature.

## Response Handling

```php
$response = Prism::structured()
    ->withSchema($schema)
    ->asStructured();

// Access the structured data as a PHP array
$data = $response->structured;

// Get the raw response text if needed
echo $response->text;

// Check why the generation stopped
echo $response->finishReason->name;

// Get token usage statistics
echo "Prompt tokens: {$response->usage->promptTokens}";
echo "Completion tokens: {$response->usage->completionTokens}";

// Access provider-specific response data
$rawResponse = $response->response;
```

> **TIP:** Always validate the structured data before using it:
> ```php
> if ($response->structured === null) {
>     // Handle parsing failure
> }
>
> if (!isset($response->structured['required_field'])) {
>     // Handle missing required data
> }
> ```

## Common Settings

### Model Configuration
- `maxTokens` - Set the maximum number of tokens to generate
- `temperature` - Control output randomness
- `topP` - Alternative to temperature for controlling randomness

### Input Methods
- `withPrompt` - Single prompt for generation
- `withMessages` - Message history for more context
- `withSystemPrompt` - System-level instructions

### Request Configuration
- `withClientOptions` - Set HTTP client options (e.g., timeouts)
- `withClientRetry` - Configure automatic retries on failures
- `usingProviderConfig` - Override provider configuration
- `withProviderOptions` - Set provider-specific options

## Combining Structured Output with Tools

You can combine structured output with tools to gather data before returning a structured response:

```php
use Prism\Prism\Tool;

$schema = new ObjectSchema(
    name: 'weather_analysis',
    description: 'Analysis of weather conditions',
    properties: [
        new StringSchema('summary', 'Summary of the weather'),
        new StringSchema('recommendation', 'Recommendation based on weather'),
    ],
    requiredFields: ['summary', 'recommendation']
);

$weatherTool = Tool::as('get_weather')
    ->for('Get current weather for a location')
    ->withStringParameter('location', 'The city and state')
    ->using(fn (string $location): string =>
        "Weather in {$location}: 72°F, sunny"
    );

$response = Prism::structured()
    ->using('anthropic', 'claude-3-5-sonnet-latest')
    ->withSchema($schema)
    ->withTools([$weatherTool])
    ->withMaxSteps(3)
    ->withPrompt('What is the weather in San Francisco and should I wear a coat?')
    ->asStructured();

dump($response->structured);
// ['summary' => '...', 'recommendation' => '...']
```

> **IMPORTANT:** When using tools with structured output, you must set `maxSteps` to at least 2. The AI needs multiple steps: one to call tools, and another to return the structured result.

### Response Handling with Tools

```php
// Final structured data
$data = $response->structured;

// All tool calls made during execution
foreach ($response->toolCalls as $toolCall) {
    echo "Called: {$toolCall->name}\n";
}

// Tool execution results
foreach ($response->toolResults as $result) {
    echo "Result: {$result->result}\n";
}
```

> **NOTE:** Only the final step contains structured data. Intermediate steps contain tool calls and results, but no structured output.
