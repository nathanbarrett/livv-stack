# Video (Input Modality)

Prism supports including video files and YouTube videos in your messages for advanced analysis with supported providers like Gemini.

See the [provider support table](../INDEX.md) to check whether Prism supports your chosen provider.

> **NOTE:** Provider support may differ by model. Check your provider's documentation if you receive error messages.

## Getting Started

Add a video to your prompt using the `withPrompt` method with a `Video` value object:

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\ValueObjects\Media\Video;

// From a local path
$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withPrompt(
        "What's in this video?",
        [Video::fromLocalPath(path: '/path/to/video.mp4')]
    )
    ->asText();

// From a path on a storage disk
$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withPrompt(
        "What's in this video?",
        [Video::fromStoragePath(
            path: '/path/to/video.mp4',
            diskName: 'my-disk' // optional - omit/null for default disk
        )]
    )
    ->asText();

// From a URL
$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withPrompt(
        'Analyze this video:',
        [Video::fromUrl(url: 'https://example.com/video.mp4')]
    )
    ->asText();

// From a YouTube URL (automatically extracts the video ID)
$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withPrompt(
        'What is this YouTube video about?',
        [Video::fromUrl(url: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')]
    )
    ->asText();

// From shortened YouTube URL
$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withPrompt(
        'What is this YouTube video about?',
        [Video::fromUrl(url: 'https://youtu.be/dQw4w9WgXcQ')]
    )
    ->asText();

// From base64
$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withPrompt(
        'Analyze this video:',
        [Video::fromBase64(
            base64: base64_encode(file_get_contents('/path/to/video.mp4')),
            mimeType: 'video/mp4'
        )]
    )
    ->asText();

// From raw content
$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withPrompt(
        'Analyze this video:',
        [Video::fromRawContent(
            rawContent: file_get_contents('/path/to/video.mp4'),
            mimeType: 'video/mp4'
        )]
    )
    ->asText();
```

## Alternative: Using withMessages

Include videos using the message-based approach:

```php
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Media\Video;

$message = new UserMessage(
    "What's in this video?",
    [Video::fromLocalPath(path: '/path/to/video.mp4')]
);

$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withMessages([$message])
    ->asText();
```

## Supported Video Types

Prism supports a variety of video formats, including:

- MP4 (video/mp4)
- MOV (video/quicktime)
- WEBM (video/webm)
- AVI (video/x-msvideo)
- YouTube videos (via URL)

The specific supported formats depend on the provider. Gemini is currently the main provider with comprehensive video analysis capabilities.

## YouTube Video Support

Prism provides seamless support for YouTube videos. When you pass a YouTube URL to `Video::fromUrl()`, Prism automatically extracts the video ID and sends it to the provider in the appropriate format.

Supported YouTube URL formats:

- Standard: `https://www.youtube.com/watch?v=VIDEO_ID`
- Shortened: `https://youtu.be/VIDEO_ID`

```php
$response = Prism::text()
    ->using(Provider::Gemini, 'gemini-1.5-flash')
    ->withPrompt(
        'What is this YouTube video about?',
        [Video::fromUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ')]
    )
    ->asText();
```

## Transfer Mediums

Providers are not consistent in their support of sending raw contents, base64 and/or URLs.

### Supported Conversions

- Where a provider does not support URLs: Prism will fetch the URL and use base64 or rawContent.
- Where you provide a file, base64 or rawContent: Prism will switch between base64 and rawContent depending on what the provider accepts.

### Limitations

- Where a provider only supports URLs: if you provide a file path, raw contents or base64, for security reasons Prism does not create a URL for you and your request will fail.
