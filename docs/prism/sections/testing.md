# Testing

Prism provides a powerful fake implementation that makes it easy to test your AI-powered features.

## Basic Test Setup

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\ValueObjects\Usage;
use Prism\Prism\Testing\TextResponseFake;

it('can generate text', function () {
    $fakeResponse = TextResponseFake::make()
        ->withText('Hello, I am Claude!')
        ->withUsage(new Usage(10, 20));

    // Set up the fake
    $fake = Prism::fake([$fakeResponse]);

    // Run your code
    $response = Prism::text()
        ->using(Provider::Anthropic, 'claude-3-5-sonnet-latest')
        ->withPrompt('Who are you?')
        ->asText();

    // Make assertions
    expect($response->text)->toBe('Hello, I am Claude!');
});
```

## Testing Multiple Responses

When testing conversations or tool usage:

```php
use Prism\Prism\ValueObjects\ToolCall;
use Prism\Prism\ValueObjects\Meta;

it('can handle tool calls', function () {
    $responses = [
        TextResponseFake::make()
            ->withToolCalls([
                new ToolCall(
                    id: 'call_1',
                    name: 'search',
                    arguments: ['query' => 'Latest news']
                )
            ])
            ->withUsage(new Usage(15, 25))
            ->withMeta(new Meta('fake-1', 'fake-model')),

        TextResponseFake::make()
            ->withText('Here are the latest news...')
            ->withUsage(new Usage(20, 30))
            ->withMeta(new Meta('fake-2', 'fake-model')),
    ];

    $fake = Prism::fake($responses);
});
```

## Using the ResponseBuilder

For testing complex responses with Steps:

```php
use Prism\Prism\Text\ResponseBuilder;
use Prism\Prism\Testing\TextStepFake;
use Prism\Prism\Enums\FinishReason;

Prism::fake([
    (new ResponseBuilder)
        ->addStep(
            TextStepFake::make()
                ->withText('Step 1 response text')
                ->withFinishReason(FinishReason::Stop)
                ->withUsage(new Usage(1000, 750))
                ->withMeta(new Meta('step1', 'test-model'))
        )
        ->addStep(
            TextStepFake::make()
                ->withText('Step 2 response text')
                ->withFinishReason(FinishReason::Stop)
                ->withUsage(new Usage(1000, 750))
                ->withMeta(new Meta('step2', 'test-model'))
        )
        ->toResponse()
]);
```

## Testing Tools

```php
use Prism\Prism\Facades\Tool;
use Prism\Prism\ValueObjects\ToolResult;

it('can use weather tool', function () {
    $responses = [
        (new ResponseBuilder)
            ->addStep(
                // First response: AI decides to use the weather tool
                TextStepFake::make()
                    ->withToolCalls([
                        new ToolCall(
                            id: 'call_123',
                            name: 'weather',
                            arguments: ['city' => 'Paris']
                        ),
                    ])
                    ->withFinishReason(FinishReason::ToolCalls)
                    ->withUsage(new Usage(15, 25))
            )
            ->addStep(
                // Second response: AI uses the tool result
                TextStepFake::make()
                    ->withText('The weather in Paris is sunny.')
                    ->withToolResults([
                        new ToolResult(
                            toolCallId: 'call_123',
                            toolName: 'weather',
                            args: ['city' => 'Paris'],
                            result: 'Sunny, 72°F'
                        ),
                    ])
                    ->withFinishReason(FinishReason::Stop)
            )
            ->toResponse(),
    ];

    Prism::fake($responses);

    $weatherTool = Tool::as('weather')
        ->for('Get weather information')
        ->withStringParameter('city', 'City name')
        ->using(fn (string $city) => "Sunny, 72°F");

    $response = Prism::text()
        ->using(Provider::Anthropic, 'claude-3-5-sonnet-latest')
        ->withPrompt('What\'s the weather in Paris?')
        ->withTools([$weatherTool])
        ->withMaxSteps(2)
        ->asText();

    expect($response->steps)->toHaveCount(2);
    expect($response->steps[0]->toolCalls[0]->name)->toBe('weather');
    expect($response->text)->toBe('The weather in Paris is sunny.');
});
```

## Testing Streamed Responses

```php
use Prism\Prism\Enums\FinishReason;

Prism::fake([
    TextResponseFake::make()
        ->withText('fake response text')
        ->withFinishReason(FinishReason::Stop),
]);

$text = Prism::text()
    ->using('anthropic', 'claude-3-sonnet')
    ->withPrompt('What is the meaning of life?')
    ->asStream();

$outputText = '';
foreach ($text as $chunk) {
    $outputText .= $chunk->text; // ['fake ', 'respo', 'nse t', 'ext', '']
}

expect($outputText)->toBe('fake response text');
```

### Adjusting Chunk Size

```php
Prism::fake([
    TextResponseFake::make()->withText('fake response text'),
])->withFakeChunkSize(1);

// Text will be streamed in chunks of one character
```

### Testing Tool Calling while Streaming

```php
Prism::fake([
    (new ResponseBuilder)
        ->addStep(
            TextStepFake::make()
                ->withToolCalls([
                    new ToolCall('id-123', 'tool', ['input' => 'value']),
                ])
        )
        ->addStep(
            TextStepFake::make()
                ->withToolResults([
                    new ToolResult('id-123', 'tool', ['input' => 'value'], 'result'),
                ])
        )
        ->addStep(
            TextStepFake::make()
                ->withText('fake response text')
        )
        ->toResponse(),
]);

$text = Prism::text()
    ->using('anthropic', 'claude-3-sonnet')
    ->withPrompt('What is the meaning of life?')
    ->asStream();

$outputText = '';
$toolCalls = [];
$toolResults = [];

foreach ($text as $chunk) {
    $outputText .= $chunk->text;

    if ($chunk->toolCalls) {
        foreach ($chunk->toolCalls as $call) {
            $toolCalls[] = $call;
        }
    }

    if ($chunk->toolResults) {
        foreach ($chunk->toolResults as $result) {
            $toolResults[] = $result;
        }
    }
}

expect($outputText)->toBe('fake response text')
    ->and($toolCalls)->toHaveCount(1)
    ->and($toolResults)->toHaveCount(1);
```

## Testing Structured Output

```php
use Prism\Prism\Testing\StructuredResponseFake;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

it('can generate structured response', function () {
    $schema = new ObjectSchema(
        name: 'user',
        description: 'A user object',
        properties: [
            new StringSchema('name', 'The user\'s name'),
            new StringSchema('bio', 'A brief bio'),
        ],
        requiredFields: ['name', 'bio']
    );

    $fakeResponse = StructuredResponseFake::make()
        ->withText(json_encode([
            'name' => 'Alice Tester',
            'bio' => 'Professional bug hunter'
        ]))
        ->withStructured([
            'name' => 'Alice Tester',
            'bio' => 'Professional bug hunter'
        ])
        ->withFinishReason(FinishReason::Stop)
        ->withUsage(new Usage(10, 20));

    Prism::fake([$fakeResponse]);

    $response = Prism::structured()
        ->using('anthropic', 'claude-3-sonnet')
        ->withPrompt('Generate a user profile')
        ->withSchema($schema)
        ->asStructured();

    expect($response->structured['name'])->toBe('Alice Tester');
    expect($response->structured['bio'])->toBe('Professional bug hunter');
});
```

## Testing Embeddings

```php
use Prism\Prism\Testing\EmbeddingsResponseFake;
use Prism\Prism\ValueObjects\Embedding;
use Prism\Prism\ValueObjects\EmbeddingsUsage;

it('can generate embeddings', function () {
    $fakeResponse = EmbeddingsResponseFake::make()
        ->withEmbeddings([Embedding::fromArray(array_fill(0, 1536, 0.1))])
        ->withUsage(new EmbeddingsUsage(10))
        ->withMeta(new Meta('fake-emb-1', 'fake-model'));

    Prism::fake([$fakeResponse]);

    $response = Prism::embeddings()
        ->using(Provider::OpenAI, 'text-embedding-3-small')
        ->fromInput('Test content for embedding generation.')
        ->asEmbeddings();

    expect($response->embeddings)->toHaveCount(1)
        ->and($response->embeddings[0]->embedding)->toHaveCount(1536);
});
```

## Assertions

`PrismFake` provides several helpful assertion methods:

```php
// Assert specific prompt was sent
$fake->assertPrompt('Who are you?');

// Assert number of calls made
$fake->assertCallCount(2);

// Assert detailed request properties
$fake->assertRequest(function ($requests) {
    expect($requests[0]->provider())->toBe('anthropic');
    expect($requests[0]->model())->toBe('claude-3-sonnet');
});

// Assert provider configuration
$fake->assertProviderConfig(['api_key' => 'sk-1234']);
```

## Using Real Response Classes

While the fake helpers are concise, you can also use real response classes:

```php
use Prism\Prism\Text\Response;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\ValueObjects\Usage;
use Prism\Prism\ValueObjects\Meta;

$response = new Response(
    steps: collect([]),
    responseMessages: collect([]),
    text: 'The meaning of life is 42',
    finishReason: FinishReason::Stop,
    toolCalls: [],
    toolResults: [],
    usage: new Usage(42, 42),
    meta: new Meta('resp_1', 'real-model'),
    messages: collect([]),
    additionalContent: [],
);
```
