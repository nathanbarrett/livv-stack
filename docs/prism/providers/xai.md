# xAI

## Configuration

```php
'xai' => [
    'api_key' => env('XAI_API_KEY', ''),
    'url' => env('XAI_URL', 'https://api.x.ai/v1'),
],
```

## Extended Thinking/Reasoning

xAI's Grok models support an optional extended thinking mode for complex reasoning tasks.

### Enabling Thinking Mode

```php
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

$response = Prism::text()
    ->using(Provider::XAI, 'grok-4')
    ->withPrompt('Solve this complex equation: 3x² + 5x - 2 = 0')
    ->withProviderOptions([
        'thinking' => ['enabled' => true]
    ])
    ->asText();
```

### Streaming Thinking Content

```php
use Prism\Prism\Enums\StreamEventType;

$stream = Prism::text()
    ->using(Provider::XAI, 'grok-4')
    ->withPrompt('Explain quantum entanglement in detail')
    ->asStream();

foreach ($stream as $event) {
    if ($event->type() === StreamEventType::ThinkingDelta) {
        echo $event->delta . PHP_EOL;
    } elseif ($event->type() === StreamEventType::TextDelta) {
        echo $event->delta;
    }
}
```

## Structured Output

xAI supports structured output through JSON schema validation with `grok-3` and `grok-4` models.

```php
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Schema\BooleanSchema;

$schema = new ObjectSchema(
    'weather_report',
    'Weather forecast with recommendations',
    [
        new StringSchema('forecast', 'The weather forecast'),
        new StringSchema('clothing', 'Clothing recommendation'),
        new BooleanSchema('coat_required', 'Whether a coat is needed'),
    ],
    ['forecast', 'clothing', 'coat_required']
);

$response = Prism::structured()
    ->withSchema($schema)
    ->using(Provider::XAI, 'grok-4')
    ->withPrompt('What\'s the weather like in Detroit?')
    ->asStructured();
```

### Strict Schema Mode

```php
$response = Prism::structured()
    ->withSchema($schema)
    ->using(Provider::XAI, 'grok-4')
    ->withProviderOptions([
        'schema' => ['strict' => true]
    ])
    ->withPrompt('Analyze this data')
    ->asStructured();
```

## Tool Calling

```php
use Prism\Prism\Facades\Tool;

$tools = [
    Tool::as('calculator')
        ->for('Perform mathematical calculations')
        ->withStringParameter('expression', 'Mathematical expression to calculate')
        ->using(fn (string $expression): string => "Result: " . eval("return $expression;")),

    Tool::as('weather')
        ->for('Get current weather information')
        ->withStringParameter('city', 'City name')
        ->using(fn (string $city): string => "Weather in {$city}: 72°F and sunny"),
];

$response = Prism::text()
    ->using(Provider::XAI, 'grok-4')
    ->withTools($tools)
    ->withMaxSteps(3)
    ->withPrompt('Calculate 15 * 23 and tell me the weather in Detroit')
    ->asText();
```

## Model Parameters

### Temperature Control

```php
$response = Prism::text()
    ->using(Provider::XAI, 'grok-4')
    ->withTemperature(0.7) // 0.0 = deterministic, 1.0 = very creative
    ->withPrompt('Write a creative story')
    ->asText();
```

### Top-P Sampling

```php
$response = Prism::text()
    ->using(Provider::XAI, 'grok-4')
    ->withTopP(0.9)
    ->withPrompt('Generate diverse responses')
    ->asText();
```

### Token Limits

```php
$response = Prism::text()
    ->using(Provider::XAI, 'grok-4')
    ->withMaxTokens(1000)
    ->withPrompt('Write a detailed explanation')
    ->asText();
```

## Advanced Examples

### Complex Analysis with Thinking

```php
$response = Prism::text()
    ->using(Provider::XAI, 'grok-4')
    ->withPrompt('Analyze the economic implications of implementing UBI.')
    ->asStream();

$analysis = '';
$reasoning = '';

foreach ($response as $chunk) {
    if ($chunk->chunkType === ChunkType::Thinking) {
        $reasoning .= $chunk->text;
    } else {
        $analysis .= $chunk->text;
        echo $chunk->text;
    }
}

file_put_contents('analysis_reasoning.txt', $reasoning);
```

### Structured Data Extraction

```php
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\IntegerSchema;
use Prism\Prism\Schema\NumberSchema;

$schema = new ObjectSchema(
    'financial_analysis',
    'Complete financial analysis result',
    [
        new StringSchema('summary', 'Executive summary'),
        new NumberSchema('total_revenue', 'Total revenue amount'),
        new NumberSchema('profit_margin', 'Profit margin percentage'),
        new ArraySchema('recommendations', 'List of recommendations',
            new StringSchema('recommendation', 'Individual recommendation')
        ),
        new ObjectSchema('risk_assessment', 'Risk analysis', [
            new StringSchema('level', 'Risk level (low/medium/high)'),
            new IntegerSchema('score', 'Risk score from 1-10'),
        ], ['level', 'score']),
    ],
    ['summary', 'total_revenue', 'profit_margin', 'recommendations', 'risk_assessment']
);
```

## Considerations

### Thinking Content Processing

- Thinking content is automatically filtered to remove repetitive patterns
- Only meaningful reasoning content is yielded
- Thinking content appears before regular response content
- Can be disabled to reduce processing overhead

### API Compatibility

xAI uses an OpenAI-compatible API structure:
- Request/response formats are similar to OpenAI
- Tool calling follows OpenAI's function calling specification
- Structured output uses JSON schema format
- Streaming follows SSE format

### Token Management

- Thinking tokens count toward total token usage
- Set appropriate `maxTokens` limits for long thinking sequences
- Monitor usage through response objects for cost tracking
