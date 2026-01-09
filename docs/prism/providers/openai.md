# OpenAI

## Configuration

```php
'openai' => [
    'url' => env('OPENAI_URL', 'https://api.openai.com/v1'),
    'api_key' => env('OPENAI_API_KEY', ''),
    'organization' => env('OPENAI_ORGANIZATION', null),
]
```

## Provider-specific Options

### Strict Tool Schemas

```php
Tool::as('search')
    ->for('Searching the web')
    ->withStringParameter('query', 'the detailed search query')
    ->using(fn (): string => '[Search results]')
    ->withProviderOptions([
      'strict' => true,
    ]);
```

### Strict Structured Output Schemas

```php
$response = Prism::structured()
    ->withProviderOptions([
        'schema' => [
            'strict' => true
        ]
    ])
```

> **Warning:** When using structured outputs with OpenAI (especially in strict mode), you must include ALL fields in the `requiredFields` array. Fields that should be optional must be marked with `nullable: true`.

### Combining Tools with Structured Output

```php
$response = Prism::structured()
    ->using('openai', 'gpt-4o')
    ->withSchema($schema)
    ->withTools([$weatherTool])
    ->withMaxSteps(3)
    ->withPrompt('What is the weather in San Francisco?')
    ->asStructured();
```

> **Important:** Set `maxSteps` to at least 2. OpenAI automatically uses the `/responses` endpoint.

### Metadata

```php
$response = Prism::structured()
    ->withProviderOptions([
        'metadata' => [
            'project_id' => 23
        ]
    ])
```

### Previous Responses

```php
$response = Prism::structured()
    ->withProviderOptions([
        'previous_response_id' => 'response_id'
    ])
```

### Service Tiers

```php
$response = Prism::text()
    ->withProviderOptions([
        'service_tier' => 'priority'
    ])
```

### Reasoning Models

OpenAI's reasoning models like `gpt-5`, `gpt-5-mini`, and `gpt-5-nano` use advanced reasoning capabilities.

#### Reasoning Effort

```php
$response = Prism::text()
    ->using('openai', 'gpt-5')
    ->withPrompt('Write a PHP function to implement binary search')
    ->withProviderOptions([
        'reasoning' => ['effort' => 'high']
    ])
    ->asText();
```

Available effort levels: `low`, `medium` (default), `high`

#### Reasoning Token Usage

```php
$usage = $response->firstStep()->usage;
echo "Reasoning tokens: " . $usage->thoughtTokens;
```

#### Text Verbosity

```php
->withProviderOptions([
    'text_verbosity' => 'low' // low, medium, high
])
```

## Streaming

```php
// Stream events
$stream = Prism::text()
    ->using('openai', 'gpt-4o')
    ->withPrompt('Write a story')
    ->asStream();

// Server-Sent Events
return Prism::text()
    ->using('openai', 'gpt-4o')
    ->withPrompt(request('message'))
    ->asEventStreamResponse();
```

### Streaming Reasoning Models

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

## Provider Tools

### Code Interpreter

```php
use Prism\Prism\ValueObjects\ProviderTool;

Prism::text()
    ->using('openai', 'gpt-4.1')
    ->withPrompt('Solve the equation 3x + 10 = 14.')
    ->withProviderTools([
        new ProviderTool(type: 'code_interpreter', options: ['container' => ['type' => 'auto']])
    ])
    ->asText();
```

## Image Generation

### Supported Models

| Model | Description |
|-------|-------------|
| `dall-e-3` | Latest DALL-E model |
| `dall-e-2` | Previous generation |
| `gpt-image-1` | GPT-based image model |

### Basic Usage

```php
$response = Prism::image()
    ->using('openai', 'dall-e-3')
    ->withPrompt('A serene mountain landscape at sunset')
    ->generate();

$image = $response->firstImage();
echo $image->url;
```

### DALL-E 3 Options

```php
$response = Prism::image()
    ->using('openai', 'dall-e-3')
    ->withPrompt('A futuristic cityscape with flying cars')
    ->withProviderOptions([
        'size' => '1792x1024',          // 1024x1024, 1024x1792, 1792x1024
        'quality' => 'hd',              // standard, hd
        'style' => 'vivid',             // vivid, natural
    ])
    ->generate();
```

### GPT-Image-1 Options

```php
$response = Prism::image()
    ->using('openai', 'gpt-image-1')
    ->withPrompt('A detailed architectural rendering')
    ->withProviderOptions([
        'size' => '1536x1024',
        'quality' => 'high',
        'output_format' => 'webp',          // png, webp, jpeg
        'output_compression' => 85,
        'background' => 'transparent',      // transparent, white, black
    ])
    ->generate();
```

### Image Editing

```php
use Prism\Prism\ValueObjects\Media\Image;

$response = Prism::image()
    ->using('openai', 'gpt-image-1')
    ->withPrompt('Add a vaporwave sunset to the background', [
        Image::fromLocalPath('tests/Fixtures/diamond.png'),
    ])
    ->withProviderOptions([
        'size' => '1024x1024',
        'output_format' => 'png',
    ])
    ->generate();
```

## Audio Processing

### Text-to-Speech

```php
$response = Prism::audio()
    ->using('openai', 'gpt-4o-mini-tts')
    ->withInput('Hello, welcome to our application!')
    ->withVoice('alloy')
    ->asAudio();

$audioData = base64_decode($response->audio->base64);
file_put_contents('welcome.mp3', $audioData);
```

#### Audio Format Options

```php
$response = Prism::audio()
    ->using('openai', 'gpt-4o-mini-tts')
    ->withInput('Testing different audio formats.')
    ->withProviderOptions([
        'voice' => 'echo',
        'response_format' => 'opus',   // mp3, opus, aac, flac, wav, pcm
        'speed' => 1.25,              // Speed: 0.25 to 4.0
    ])
    ->asAudio();
```

### Speech-to-Text

```php
use Prism\Prism\ValueObjects\Media\Audio;

$audioFile = Audio::fromPath('/path/to/recording.mp3');

$response = Prism::audio()
    ->using('openai', 'whisper-1')
    ->withInput($audioFile)
    ->asText();

echo "Transcription: " . $response->text;
```

#### Response Formats

```php
// Verbose JSON includes timestamps
$response = Prism::audio()
    ->using('openai', 'whisper-1')
    ->withInput($audioFile)
    ->withProviderOptions([
        'response_format' => 'verbose_json',
    ])
    ->asText();

$segments = $response->additionalContent['segments'] ?? [];
```

## Moderation

### Text Moderation

```php
$response = Prism::moderation()
    ->using(Provider::OpenAI)
    ->withInput('Your text to check goes here')
    ->asModeration();

if ($response->isFlagged()) {
    $flagged = $response->firstFlagged();
}
```

### Image Moderation

```php
$response = Prism::moderation()
    ->using(Provider::OpenAI, 'omni-moderation-latest')
    ->withInput(Image::fromUrl('https://example.com/image.png'))
    ->asModeration();
```

### Mixed Moderation

```php
$response = Prism::moderation()
    ->using(Provider::OpenAI, 'omni-moderation-latest')
    ->withInput(
        'Check this text',
        Image::fromStoragePath('uploads/user-photo.jpg', 'public'),
        'Another text to check'
    )
    ->asModeration();
```
