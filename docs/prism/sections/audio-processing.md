# Audio Processing

Transform text into speech and speech into text using AI-powered audio models. Prism provides a unified API for audio processing across different providers.

## Getting Started

### Text-to-Speech

Convert text into natural-sounding speech:

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

$response = Prism::audio()
    ->using(Provider::OpenAI, 'tts-1')
    ->withInput('Hello, this is a test of text-to-speech functionality.')
    ->withVoice('alloy')
    ->asAudio();

$audio = $response->audio;
if ($audio->hasBase64()) {
    file_put_contents('output.mp3', base64_decode($audio->base64));
    echo "Audio saved as: output.mp3";
}
```

### Speech-to-Text

Convert audio files into text transcriptions:

```php
use Prism\Prism\ValueObjects\Media\Audio;

$audioFile = Audio::fromPath('/path/to/audio.mp3');

$response = Prism::audio()
    ->using(Provider::OpenAI, 'whisper-1')
    ->withInput($audioFile)
    ->asText();

echo "Transcription: " . $response->text;
```

## Provider Support

Currently supported:

- **OpenAI**: TTS-1, TTS-1-HD (text-to-speech) and Whisper-1 (speech-to-text)
- **Groq**: PlayAI TTS models (text-to-speech) and Whisper Large V3 models (speech-to-text)

## Working with Audio Files

### Creating Audio Objects

```php
use Prism\Prism\ValueObjects\Media\Audio;

// From local file
$audio = Audio::fromPath('/path/to/audio.mp3');

// From remote URL
$audio = Audio::fromUrl('https://example.com/speech.wav');

// From base64 encoded data
$audio = Audio::fromBase64($base64Data, 'audio/mpeg');

// From raw binary content
$audio = Audio::fromContent($binaryData, 'audio/wav');
```

### Audio Properties

```php
$audio = Audio::fromPath('/path/to/audio.mp3');

echo "MIME type: " . $audio->mimeType();
echo "Has local path: " . ($audio->hasLocalPath() ? 'Yes' : 'No');
echo "File size: " . $audio->size() . " bytes";
```

## Working with Responses

### Text-to-Speech Responses

```php
$response = Prism::audio()
    ->using('openai', 'tts-1-hd')
    ->withInput('This is high-quality text-to-speech.')
    ->withVoice('nova')
    ->asAudio();

$audio = $response->audio;

if ($audio->hasBase64()) {
    // Save to file
    $audioData = base64_decode($audio->base64);
    file_put_contents('speech.mp3', $audioData);

    // Get MIME type
    echo "Content type: " . $audio->getMimeType();
}
```

### Speech-to-Text Responses

```php
$response = Prism::audio()
    ->using('openai', 'whisper-1')
    ->withInput($audioFile)
    ->asText();

$text = $response->text;
echo "Transcription: " . $text;

// Check token usage
if ($response->usage) {
    echo "Prompt tokens: " . $response->usage->promptTokens;
}
```

## Voice Selection

```php
$response = Prism::audio()
    ->using('openai', 'tts-1')
    ->withInput('Hello, how are you today?')
    ->withVoice('alloy')  // Voice options vary by provider
    ->asAudio();
```

## Provider-Specific Options

### OpenAI Text-to-Speech Options

```php
$response = Prism::audio()
    ->using('openai', 'tts-1')
    ->withInput('Hello, how are you today?')
    ->withVoice('nova')
    ->withProviderOptions([
        'response_format' => 'mp3',  // mp3, opus, aac, flac, wav, pcm
        'speed' => 1.0,              // 0.25 to 4.0
    ])
    ->asAudio();
```

### OpenAI Speech-to-Text Options

```php
$response = Prism::audio()
    ->using('openai', 'whisper-1')
    ->withInput($audioFile)
    ->withProviderOptions([
        'language' => 'en',
        'prompt' => 'Previous context...'
    ])
    ->asText();
```

### Response Formats

Different response formats provide varying levels of detail:

```php
// Verbose JSON format includes timestamps and confidence scores
$response = Prism::audio()
    ->using('openai', 'whisper-1')
    ->withInput($audioFile)
    ->withProviderOptions([
        'response_format' => 'verbose_json',
    ])
    ->asText();

$metadata = $response->additionalContent;
if (isset($metadata['segments'])) {
    foreach ($metadata['segments'] as $segment) {
        echo "Segment: " . $segment['text'] . "\n";
        echo "Start: " . $segment['start'] . "s\n";
        echo "End: " . $segment['end'] . "s\n";
    }
}
```

## Advanced Usage

Integrate audio into multi-modal conversations:

```php
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Media\Text;

// First transcribe the audio
$transcription = Prism::audio()
    ->using('openai', 'whisper-1')
    ->withInput($audioFile)
    ->asText();

// Then use in a text conversation
$response = Prism::text()
    ->using('openai', 'gpt-4')
    ->withMessages([
        new UserMessage('', [
            new Text('User asked: '),
            new Text($transcription->text),
            new Text(' - Please provide a detailed response.')
        ])
    ])
    ->asText();

// Convert response back to speech
$speechResponse = Prism::audio()
    ->using('openai', 'tts-1')
    ->withInput($response->text)
    ->withVoice('alloy')
    ->asAudio();
```

## Configuration Options

### Client Configuration

```php
$response = Prism::audio()
    ->using('openai', 'tts-1')
    ->withInput('This might take a while to process.')
    ->withClientOptions([
        'timeout' => 60,
        'connect_timeout' => 10,
    ])
    ->withClientRetry(3, 1000)
    ->asAudio();
```

### Provider Configuration

Override provider configuration for multi-tenant applications:

```php
$response = Prism::audio()
    ->using('openai', 'whisper-1')
    ->usingProviderConfig([
        'api_key' => 'user-specific-api-key',
        'organization' => 'user-org-id',
    ])
    ->withInput($audioFile)
    ->asText();
```

## Error Handling

```php
use Prism\Prism\Exceptions\PrismException;

try {
    $response = Prism::audio()
        ->using('openai', 'tts-1')
        ->withInput('Text to convert to speech')
        ->withVoice('alloy')
        ->asAudio();

    file_put_contents('output.mp3', base64_decode($response->audio->base64));

} catch (PrismException $e) {
    Log::error('Audio processing failed:', ['error' => $e->getMessage()]);
}
```

## Testing

```php
use Prism\Prism\Testing\PrismFake;
use Prism\Prism\Audio\AudioResponse;
use Prism\Prism\Audio\TextResponse;
use Prism\Prism\ValueObjects\GeneratedAudio;

test('can generate text-to-speech', function () {
    $fakeAudio = new AudioResponse(
        audio: new GeneratedAudio(
            base64: base64_encode('fake-audio-data'),
            type: 'audio/mpeg'
        )
    );

    Prism::fake([$fakeAudio]);

    $response = Prism::audio()
        ->using('openai', 'tts-1')
        ->withInput('Test audio generation')
        ->withVoice('alloy')
        ->asAudio();

    expect($response->audio->hasBase64())->toBeTrue();
});

test('can transcribe speech-to-text', function () {
    $fakeTranscription = new TextResponse(
        text: 'This is a fake transcription'
    );

    Prism::fake([$fakeTranscription]);

    $response = Prism::audio()
        ->using('openai', 'whisper-1')
        ->withInput($audioFile)
        ->asText();

    expect($response->text)->toBe('This is a fake transcription');
});
```
