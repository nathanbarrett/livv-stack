# Gemini

## Configuration

```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY', ''),
    'url' => env('GEMINI_URL', 'https://generativelanguage.googleapis.com/v1beta/models'),
],
```

## Search Grounding

Google Gemini offers built-in search grounding capabilities for real-time web information.

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\ValueObjects\ProviderTool;

$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-2.0-flash')
    ->withPrompt('What is the stock price of Google right now?')
    ->withProviderTools([
        new ProviderTool('google_search')
    ])
    ->asText();
```

Access search data via `additionalContent`:

```php
$response->additionalContent['searchEntryPoint'];  // Google styled widget
$response->additionalContent['searchQueries'];     // Search queries made
$response->additionalContent['citations'];         // Citations data
```

### Building Footnotes

```php
use Prism\Prism\ValueObjects\MessagePartWithCitations;
use Prism\Prism\ValueObjects\Citation;

$text = '';
$footnotes = [];
$footnoteId = 1;

foreach ($response->additionalContent['citations'] as $part) {
    $text .= $part->outputText;

    foreach ($part->citations as $citation) {
        $footnotes[] = [
            'id' => $footnoteId,
            'title' => $citation->sourceTitle,
            'uri' => $citation->source,
        ];
        $text .= '<sup><a href="#footnote-'.$footnoteId.'">'.$footnoteId.'</a></sup>';
        $footnoteId++;
    }
}
```

## Structured Output

```php
$schema = new ObjectSchema(
    name: 'movie_review',
    description: 'A structured movie review',
    properties: [
        new StringSchema('title', 'The movie title'),
        new StringSchema('rating', 'Rating out of 5 stars'),
        new StringSchema('summary', 'Brief review summary'),
    ],
    requiredFields: ['title', 'rating', 'summary']
);

$response = Prism::structured()
    ->using(Provider::Gemini, 'gemini-2.0-flash')
    ->withSchema($schema)
    ->withPrompt('Review the movie Inception')
    ->asStructured();
```

### Flexible Types with anyOf

For polymorphic data (requires Gemini 2.5+):

```php
use Prism\Prism\Schema\AnyOfSchema;

$schema = new ObjectSchema(
    'response',
    'API response with flexible value',
    [
        new AnyOfSchema(
            schemas: [
                new StringSchema('text', 'Text value'),
                new NumberSchema('number', 'Numeric value'),
            ],
            name: 'value',
            description: 'Can be either text or number'
        ),
    ],
    ['value']
);
```

### Numeric Constraints

```php
new NumberSchema(
    name: 'rating',
    description: 'User rating (1-5 stars)',
    minimum: 1.0,
    maximum: 5.0,
    multipleOf: 0.5
),
```

Available constraints: `minimum`, `maximum`, `exclusiveMinimum`, `exclusiveMaximum`, `multipleOf`

### Combining Tools with Structured Output

```php
$response = Prism::structured()
    ->using('gemini', 'gemini-2.0-flash')
    ->withSchema($schema)
    ->withTools([$weatherTool])
    ->withMaxSteps(3)
    ->withPrompt('What is the weather in San Francisco?')
    ->asStructured();
```

## Caching

Store content in cache:

```php
use Prism\Prism\Providers\Gemini\Gemini;

/** @var Gemini */
$provider = Prism::provider(Provider::Gemini);

$object = $provider->cache(
    model: 'gemini-1.5-flash-002',
    messages: [
        new UserMessage('', [
            Document::fromLocalPath('tests/Fixtures/long-document.pdf'),
        ]),
    ],
    systemPrompts: [
        new SystemMessage('You are a legal analyst.'),
    ],
    ttl: 60
);
```

Reference in request:

```php
$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash-002')
    ->withProviderOptions(['cachedContentName' => $object->name])
    ->withPrompt('What is the document about?')
    ->asText();
```

## Embeddings

### Title

```php
Prism::embeddings()
    ->using(Provider::Gemini, 'text-embedding-004')
    ->fromInput('The food was delicious...')
    ->withProviderOptions(['title' => 'Restaurant Review'])
    ->asEmbeddings();
```

### Task Type

```php
Prism::embeddings()
    ->using(Provider::Gemini, 'text-embedding-004')
    ->fromInput('The food was delicious...')
    ->withProviderOptions(['taskType' => 'RETRIEVAL_QUERY'])
    ->asEmbeddings();
```

### Output Dimensionality

```php
->withProviderOptions(['outputDimensionality' => 768])
```

## Thinking Mode

Gemini 2.5 series models use an internal thinking process. Customize with `thinkingBudget`:

```php
$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-2.5-flash-preview')
    ->withPrompt('Explain the concept of Occam\'s Razor')
    ->withProviderOptions(['thinkingBudget' => 300])
    ->asText();
```

Set to `0` to disable thinking. Do not use on 2.0 or prior models.

## Streaming

```php
return Prism::text()
    ->using('gemini', 'gemini-2.5-flash-preview')
    ->withPrompt(request('message'))
    ->asEventStreamResponse();
```

### Streaming with Thinking

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

## Media Support

### Video Analysis

```php
use Prism\Prism\ValueObjects\Media\Video;

$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withMessages([
        new UserMessage(
            'What is happening in this video?',
            additionalContent: [
                Video::fromUrl('https://example.com/sample-video.mp4'),
            ],
        ),
    ])
    ->asText();
```

### YouTube Integration

```php
Video::fromUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
```

### Audio Processing

```php
use Prism\Prism\ValueObjects\Media\Audio;

$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withMessages([
        new UserMessage(
            'Transcribe this audio file:',
            additionalContent: [
                Audio::fromLocalPath('/path/to/audio.mp3'),
            ],
        ),
    ])
    ->asText();
```

## Image Generation

### Supported Models

| Model | Description |
|-------|-------------|
| `gemini-2.0-flash-preview-image-generation` | Experimental gemini image generation |
| `imagen-4.0-generate-001` | Latest Imagen model |
| `imagen-4.0-ultra-generate-001` | Highest quality (1 image per request) |
| `imagen-4.0-fast-generate-001` | Fastest Imagen 4 model |
| `imagen-3.0-generate-002` | Imagen 3 |

### Basic Usage

```php
$response = Prism::image()
    ->using(Provider::Gemini, 'gemini-2.0-flash-preview-image-generation')
    ->withPrompt('Generate an image of ducklings wearing rubber boots')
    ->generate();

file_put_contents('image.png', base64_decode($response->firstImage()->base64));
```

### Image Editing

```php
$originalImage = fopen('image/boots.png', 'r');

$response = Prism::image()
    ->using(Provider::Gemini, 'gemini-2.0-flash-preview-image-generation')
    ->withPrompt('Actually, could we make those boots red?')
    ->withProviderOptions([
        'image' => $originalImage,
        'image_mime_type' => 'image/png',
    ])
    ->generate();
```

### Imagen Options

```php
$response = Prism::image()
    ->using(Provider::Gemini, 'imagen-4.0-generate-001')
    ->withPrompt('Generate an image of a magnificent building')
    ->withProviderOptions([
        'n' => 3,                               // number of images
        'size' => '2K',                         // 1K (default), 2K
        'aspect_ratio' => '16:9',               // 1:1, 3:4, 4:3, 9:16, 16:9
        'person_generation' => 'dont_allow',    // dont_allow, allow_adult, allow_all
    ])
    ->generate();
```
