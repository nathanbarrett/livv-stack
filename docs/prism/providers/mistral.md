# Mistral

## Configuration

```php
'mistral' => [
    'api_key' => env('MISTRAL_API_KEY', ''),
    'url' => env('MISTRAL_URL', 'https://api.mistral.ai/v1'),
],
```

## Reasoning Models

### Using Reasoning Models

Simply specify a reasoning model. The thinking process is automatically included:

```php
use Prism\Prism\Facades\Prism;

$response = Prism::text()
    ->using('mistral', 'magistral-medium-latest')
    ->withPrompt('What is the capital of France?')
    ->asText();

// Access the final answer
echo $response->text;

// Access the reasoning process
echo $response->additionalContent['thinking'];
```

### Accessing Thinking Content

On the Response:

```php
$response = Prism::text()
    ->using('mistral', 'magistral-medium-latest')
    ->withPrompt('What is the meaning of life in popular fiction?')
    ->asText();

$thinking = $response->additionalContent['thinking'];
$answer = $response->text;
```

On the Step (when using tools):

```php
$response = Prism::text()
    ->using('mistral', 'magistral-medium-latest')
    ->withTools($tools)
    ->withMaxSteps(3)
    ->withPrompt('What time is the Tigers game today?')
    ->asText();

$thinking = $response->steps->first()->additionalContent['thinking'];
```

### Response Structure

Reasoning models return:
1. **Thinking blocks** - Internal reasoning (stored in `additionalContent['thinking']`)
2. **Text blocks** - Final answer (stored in `text`)

## Streaming

```php
return Prism::text()
    ->using('mistral', 'mistral-large-latest')
    ->withPrompt(request('message'))
    ->asEventStreamResponse();
```

## Audio Processing

Mistral provides advanced speech-to-text through Voxtral models.

### Speech-to-Text

#### Basic Usage

```php
use Prism\Prism\ValueObjects\Media\Audio;

$audioFile = Audio::fromPath('/path/to/recording.mp3');

$response = Prism::audio()
    ->using('mistral', 'voxtral-mini-2507')
    ->withInput($audioFile)
    ->asText();

echo "Transcription: " . $response->text;
```

#### Model Selection

```php
// Highest accuracy
$response = Prism::audio()
    ->using('mistral', 'voxtral-small-latest')
    ->withInput($audioFile)
    ->asText();

// Efficient transcription
$response = Prism::audio()
    ->using('mistral', 'voxtral-mini-latest')
    ->withInput($audioFile)
    ->asText();

// Optimized transcription-only
$response = Prism::audio()
    ->using('mistral', 'voxtral-mini-2507')
    ->withInput($audioFile)
    ->asText();
```

#### Language Detection

```php
$response = Prism::audio()
    ->using('mistral', 'voxtral-mini-2507')
    ->withInput($audioFile)
    ->withProviderOptions([
        'language' => 'en',
        'temperature' => 0.0,
    ])
    ->asText();
```

#### Timestamps and Segmentation

```php
$response = Prism::audio()
    ->using('mistral', 'voxtral-mini-2507')
    ->withInput($audioFile)
    ->withProviderOptions([
        'timestamp_granularities' => ['segment'],
        'response_format' => 'json',
    ])
    ->asText();

$segments = $response->additionalContent['segments'] ?? [];
foreach ($segments as $segment) {
    echo "Text: " . $segment['text'] . "\n";
    echo "Start: " . $segment['start'] . "s\n";
    echo "End: " . $segment['end'] . "s\n";
}
```

#### Long-form Audio

Voxtral handles up to 30 minutes in a single request:

```php
$longAudioFile = Audio::fromPath('/path/to/long_meeting.wav');

$response = Prism::audio()
    ->using('mistral', 'voxtral-small-latest')
    ->withInput($longAudioFile)
    ->withProviderOptions([
        'timestamp_granularities' => ['segment'],
        'language' => 'en',
    ])
    ->asText();
```

## Documents

Text generation only allows documents via URL. See the [documents](../sections/input-documents.md) section.

## OCR

Mistral provides an OCR endpoint for text extraction:

```php
use Prism\Prism\Providers\Mistral\Mistral;
use Prism\Prism\Providers\Mistral\ValueObjects\OCRResponse;

/** @var Mistral $provider */
$provider = Prism::provider(\Prism\Prism\Enums\Provider::Mistral);

/** @var OCRResponse $ocrResponse */
$ocrResponse = $provider->ocr(
    'mistral-ocr-latest',
    Document::fromUrl('https://prismphp.com/storage/prism-text-generation.pdf')
);

// Get full text of all pages
$text = $ocrResponse->toText();
```

> **Tip:** The OCR endpoint response time can vary. Consider using a queue with a longer timeout.
