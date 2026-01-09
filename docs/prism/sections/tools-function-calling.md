# Tools & Function Calling

Tools let you extend your AI's capabilities by giving it access to specific functions it can call.

## Tool Concept Overview

Think of tools as special functions that your AI assistant can use when it needs to perform specific tasks:

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Tool;

$weatherTool = Tool::as('weather')
    ->for('Get current weather conditions')
    ->withStringParameter('city', 'The city to get weather for')
    ->using(function (string $city): string {
        // Your weather API logic here
        return "The weather in {$city} is sunny and 72°F.";
    });

$response = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-latest')
    ->withMaxSteps(2)
    ->withPrompt('What is the weather like in Paris?')
    ->withTools([$weatherTool])
    ->asText();
```

## Max Steps

Prism defaults to a single step. To use Tools, increase this using `withMaxSteps`:

```php
Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-latest')
    ->withMaxSteps(2) // At least 2 for tool usage
    ->withPrompt('What is the weather like in Paris?')
    ->withTools([$weatherTool])
    ->asText();
```

Use a higher number if you expect multiple tool calls.

## Creating Basic Tools

```php
use Prism\Prism\Facades\Tool;

$searchTool = Tool::as('search')
    ->for('Search for current information')
    ->withStringParameter('query', 'The search query')
    ->using(function (string $query): string {
        // Your search implementation
        return "Search results for: {$query}";
    });
```

Tools can take a variety of parameters, but must always return a string.

## Error Handling

By default, tools handle invalid parameters gracefully by returning error messages:

```php
$tool = Tool::as('calculate')
    ->for('Add two numbers')
    ->withNumberParameter('a', 'First number')
    ->withNumberParameter('b', 'Second number')
    ->using(fn (int $a, int $b): string => (string) ($a + $b));

// If AI provides invalid parameters, it receives:
// "Parameter validation error: Type mismatch..."
```

### Opting Out

If you prefer exceptions for invalid parameters:

```php
// Per-tool
$tool->withoutErrorHandling();

// Per-request
Prism::text()->withoutToolErrorHandling();
```

## Parameter Types

### String Parameters

```php
$tool = Tool::as('search')
    ->for('Search for information')
    ->withStringParameter('query', 'The search query')
    ->using(function (string $query): string {
        return "Search results for: {$query}";
    });
```

### Number Parameters

```php
$tool = Tool::as('calculate')
    ->for('Perform calculations')
    ->withNumberParameter('value', 'The number to process')
    ->using(function (float $value): string {
        return "Calculated result: {$value * 2}";
    });
```

### Boolean Parameters

```php
$tool = Tool::as('feature_toggle')
    ->for('Toggle a feature')
    ->withBooleanParameter('enabled', 'Whether to enable the feature')
    ->using(function (bool $enabled): string {
        return "Feature is now " . ($enabled ? 'enabled' : 'disabled');
    });
```

### Array Parameters

```php
use Prism\Prism\Schema\StringSchema;

$tool = Tool::as('process_tags')
    ->for('Process a list of tags')
    ->withArrayParameter(
        'tags',
        'List of tags to process',
        new StringSchema('tag', 'A single tag')
    )
    ->using(function (array $tags): string {
        return "Processing tags: " . implode(', ', $tags);
    });
```

### Enum Parameters

```php
$tool = Tool::as('set_status')
    ->for('Set the status')
    ->withEnumParameter(
        'status',
        'The new status',
        ['draft', 'published', 'archived']
    )
    ->using(function (string $status): string {
        return "Status set to: {$status}";
    });
```

### Object Parameters

```php
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Schema\NumberSchema;

$tool = Tool::as('update_user')
    ->for('Update a user profile')
    ->withObjectParameter(
        'user',
        'The user profile data',
        [
            new StringSchema('name', 'User\'s full name'),
            new NumberSchema('age', 'User\'s age'),
            new StringSchema('email', 'User\'s email address')
        ],
        requiredFields: ['name', 'email']
    )
    ->using(function (array $user): string {
        return "Updated user profile for: {$user['name']}";
    });
```

## Complex Tool Implementation

For more sophisticated tools, create dedicated classes:

```php
namespace App\Tools;

use Prism\Prism\Tool;
use Illuminate\Support\Facades\Http;

class SearchTool extends Tool
{
    public function __construct()
    {
        $this
            ->as('search')
            ->for('useful when you need to search for current events')
            ->withStringParameter('query', 'Detailed search query.')
            ->using($this);
    }

    public function __invoke(string $query): string
    {
        $response = Http::get('https://serpapi.com/search', [
            'engine' => 'google',
            'q' => $query,
            'api_key' => config('services.serpapi.api_key'),
        ]);

        $results = collect($response->json('organic_results'))
            ->map(fn ($result) => [
                'title' => $result['title'],
                'link' => $result['link'],
                'snippet' => $result['snippet'],
            ])
            ->take(4);

        return view('prompts.search-tool-results', [
            'results' => $results,
        ])->render();
    }
}
```

Use `Tool::make($className)` to resolve dependencies:

```php
$tool = Tool::make(SearchTool::class);
```

## Using Laravel MCP Tools

You can use existing Laravel MCP Tools in Prism directly:

```php
use App\Mcp\Tools\CurrentWeatherTool;
use Prism\Prism\Facades\Tool;

$tool = Tool::make(CurrentWeatherTool::class);
```

## Tool Choice Options

Control how the AI uses tools:

```php
use Prism\Prism\Enums\ToolChoice;

$prism = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-latest')
    ->withMaxSteps(2)
    ->withPrompt('How is the weather in Paris?')
    ->withTools([$weatherTool])
    // Let the AI decide whether to use tools
    ->withToolChoice(ToolChoice::Auto)
    // Force the AI to use a tool
    ->withToolChoice(ToolChoice::Any)
    // Force the AI to use a specific tool
    ->withToolChoice('weather');
```

> **WARNING:** Tool choice support varies by provider.

## Response Handling with Tools

```php
$response = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-latest')
    ->withMaxSteps(2)
    ->withPrompt('What is the weather like in Paris?')
    ->withTools([$weatherTool])
    ->asText();

// Get the final answer
echo $response->text;

// Inspect tool usage
if ($response->toolResults) {
    foreach ($response->toolResults as $toolResult) {
        echo "Tool: " . $toolResult->toolName . "\n";
        echo "Result: " . $toolResult->result . "\n";
    }
}

foreach ($response->steps as $step) {
    if ($step->toolCalls) {
        foreach ($step->toolCalls as $toolCall) {
            echo "Tool: " . $toolCall->name . "\n";
            echo "Arguments: " . json_encode($toolCall->arguments()) . "\n";
        }
    }
}
```

## Tool Artifacts

Sometimes tools need to produce binary data like images, audio, or files alongside their text response:

```php
use Prism\Prism\ValueObjects\Artifact;
use Prism\Prism\ValueObjects\ToolOutput;

$imageTool = Tool::as('generate_image')
    ->for('Generate an image from a prompt')
    ->withStringParameter('prompt', 'The image prompt')
    ->using(function (string $prompt): ToolOutput {
        $imageData = $this->imageGenerator->generate($prompt);

        return new ToolOutput(
            result: json_encode(['status' => 'success', 'description' => $prompt]),
            artifacts: [
                Artifact::fromRawContent(
                    content: $imageData,
                    mimeType: 'image/png',
                    metadata: ['width' => 1024, 'height' => 1024],
                    id: 'generated-image-001',
                ),
            ],
        );
    });
```

The `result` goes to the LLM. The `artifacts` travel through the streaming system to your application.

## Provider Tools

Provider tools are built-in capabilities offered directly by AI providers:

```php
use Prism\Prism\ValueObjects\ProviderTool;

$response = Prism::text()
    ->using('anthropic', 'claude-3-5-sonnet-latest')
    ->withPrompt('Calculate the fibonacci sequence up to 100')
    ->withProviderTools([
        new ProviderTool(type: 'code_execution_20250522', name: 'code_execution')
    ])
    ->asText();
```

### Combining Provider Tools and Custom Tools

```php
$customTool = Tool::as('database_lookup')
    ->for('Look up user information')
    ->withStringParameter('user_id', 'The user ID to look up')
    ->using(function (string $userId): string {
        return "User data for ID: {$userId}";
    });

$response = Prism::text()
    ->using('anthropic', 'claude-3-5-sonnet-latest')
    ->withMaxSteps(5)
    ->withPrompt('Look up user 123 and calculate their usage statistics')
    ->withTools([$customTool])
    ->withProviderTools([
        new ProviderTool(type: 'code_execution_20250522', name: 'code_execution')
    ])
    ->asText();
```
