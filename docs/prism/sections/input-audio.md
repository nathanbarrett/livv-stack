# Audio (Input Modality)

Prism supports including audio files in your messages for advanced analysis with supported providers like Gemini.

See the [provider support table](../INDEX.md) to check whether Prism supports your chosen provider.

> **NOTE:** Provider support may differ by model. Check your provider's documentation if you receive error messages.

## Getting Started

Add an audio file to your prompt using the `withPrompt` method with an `Audio` value object:

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\ValueObjects\Media\Audio;

// From a local path
$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withPrompt(
        "What's in this audio?",
        [Audio::fromLocalPath(path: '/path/to/audio.mp3')]
    )
    ->asText();

// From a path on a storage disk
$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withPrompt(
        "What's in this audio?",
        [Audio::fromStoragePath(
            path: '/path/to/audio.mp3',
            diskName: 'my-disk' // optional - omit/null for default disk
        )]
    )
    ->asText();

// From a URL
$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withPrompt(
        'Analyze this audio:',
        [Audio::fromUrl(url: 'https://example.com/audio.mp3')]
    )
    ->asText();

// From base64
$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withPrompt(
        'Analyze this audio:',
        [Audio::fromBase64(
            base64: base64_encode(file_get_contents('/path/to/audio.mp3')),
            mimeType: 'audio/mpeg'
        )]
    )
    ->asText();

// From raw content
$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withPrompt(
        'Analyze this audio:',
        [Audio::fromRawContent(
            rawContent: file_get_contents('/path/to/audio.mp3'),
            mimeType: 'audio/mpeg'
        )]
    )
    ->asText();
```

## Alternative: Using withMessages

Include audio files using the message-based approach:

```php
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Media\Audio;

$message = new UserMessage(
    "What's in this audio?",
    [Audio::fromLocalPath(path: '/path/to/audio.mp3')]
);

$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withMessages([$message])
    ->asText();
```

## Supported Audio Types

Prism supports a variety of audio formats, including:

- MP3 (audio/mpeg)
- WAV (audio/x-wav, audio/wav)
- AAC (audio/aac)
- FLAC (audio/flac)

The specific supported formats depend on the provider. Gemini is currently the main provider with comprehensive audio analysis capabilities.

## Transfer Mediums

Providers are not consistent in their support of sending raw contents, base64 and/or URLs.

### Supported Conversions

- Where a provider does not support URLs: Prism will fetch the URL and use base64 or rawContent.
- Where you provide a file, base64 or rawContent: Prism will switch between base64 and rawContent depending on what the provider accepts.

### Limitations

- Where a provider only supports URLs: if you provide a file path, raw contents or base64, for security reasons Prism does not create a URL for you and your request will fail.
