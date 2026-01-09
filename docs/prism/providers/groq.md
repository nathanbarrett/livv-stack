# Groq

## Configuration

```php
'groq' => [
    'api_key' => env('GROQ_API_KEY', ''),
    'url' => env('GROQ_URL', 'https://api.groq.com/openai/v1'),
],
```

## Streaming

Groq's ultra-fast LPU architecture provides exceptional streaming performance:

```php
return Prism::text()
    ->using('groq', 'llama-3.3-70b-versatile')
    ->withPrompt(request('message'))
    ->asEventStreamResponse();
```

## Audio Processing

Groq provides high-performance audio processing through their LPU architecture.

### Text-to-Speech

#### Basic Usage

```php
use Prism\Prism\Facades\Prism;

$response = Prism::audio()
    ->using('groq', 'playai-tts')
    ->withInput('Hello, welcome to our application!')
    ->withVoice('Fritz-PlayAI')
    ->asAudio();

$audioData = base64_decode($response->audio->base64);
file_put_contents('welcome.wav', $audioData);
```

#### TTS Configuration Options

```php
$response = Prism::audio()
    ->using('groq', 'playai-tts')
    ->withInput('Testing different audio settings.')
    ->withVoice('Celeste-PlayAI')
    ->withProviderOptions([
        'response_format' => 'wav',
        'speed' => 1.2,                    // Speed: 0.5 to 5.0
        'sample_rate' => 48000,            // 8000, 16000, 22050, 24000, 32000, 44100, 48000
    ])
    ->asAudio();
```

#### Arabic Text-to-Speech

```php
$response = Prism::audio()
    ->using('groq', 'playai-tts-arabic')
    ->withInput('مرحبا بكم في تطبيقنا')
    ->withVoice('Amira-PlayAI')
    ->asAudio();
```

### Speech-to-Text

Groq provides ultra-fast speech recognition using Whisper models with real-time factors of up to 299x.

#### Basic Usage

```php
use Prism\Prism\ValueObjects\Media\Audio;

$audioFile = Audio::fromPath('/path/to/recording.mp3');

$response = Prism::audio()
    ->using('groq', 'whisper-large-v3')
    ->withInput($audioFile)
    ->asText();

echo "Transcription: " . $response->text;
```

#### Model Selection Guide

```php
// Highest accuracy
$response = Prism::audio()
    ->using('groq', 'whisper-large-v3')
    ->withInput($audioFile)
    ->asText();

// Fastest English-only transcription
$response = Prism::audio()
    ->using('groq', 'distil-whisper-large-v3-en')
    ->withInput($audioFile)
    ->asText();

// Balanced speed and multilingual capability
$response = Prism::audio()
    ->using('groq', 'whisper-large-v3-turbo')
    ->withInput($audioFile)
    ->asText();
```

#### Language Detection

```php
$response = Prism::audio()
    ->using('groq', 'whisper-large-v3')
    ->withInput($audioFile)
    ->withProviderOptions([
        'language' => 'es',           // ISO-639-1 code
        'temperature' => 0.2,
    ])
    ->asText();
```

#### Response Formats

```php
// Standard JSON response
$response = Prism::audio()
    ->using('groq', 'whisper-large-v3')
    ->withInput($audioFile)
    ->withProviderOptions([
        'response_format' => 'json',  // json, text, verbose_json
    ])
    ->asText();

// Verbose JSON with timestamps
$response = Prism::audio()
    ->using('groq', 'whisper-large-v3')
    ->withInput($audioFile)
    ->withProviderOptions([
        'response_format' => 'verbose_json',
        'timestamp_granularities' => ['segment'],
    ])
    ->asText();

// Access segment information
$segments = $response->additionalContent['segments'] ?? [];
foreach ($segments as $segment) {
    echo "Text: " . $segment['text'] . "\n";
    echo "Start: " . $segment['start'] . "s\n";
    echo "End: " . $segment['end'] . "s\n";
}
```

#### Context and Prompts

```php
$response = Prism::audio()
    ->using('groq', 'whisper-large-v3')
    ->withInput($audioFile)
    ->withProviderOptions([
        'prompt' => 'This is a technical discussion about machine learning.',
        'language' => 'en',
        'temperature' => 0.1,
    ])
    ->asText();
```

#### Creating Audio Objects

```php
use Prism\Prism\ValueObjects\Media\Audio;

// From local file path
$audio = Audio::fromPath('/path/to/audio.mp3');

// From remote URL (recommended for large files)
$audio = Audio::fromUrl('https://example.com/recording.wav');

// From base64 encoded data
$audio = Audio::fromBase64($base64AudioData, 'audio/mpeg');

// From binary content
$audioContent = file_get_contents('/path/to/audio.wav');
$audio = Audio::fromContent($audioContent, 'audio/wav');
```
