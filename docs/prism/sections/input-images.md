# Images (Input Modality)

Prism supports including images in your messages for vision analysis with most providers.

See the [provider support table](../INDEX.md) to check whether Prism supports your chosen provider.

> **NOTE:** Provider support may differ by model. Check your provider's documentation if you receive error messages.

## Getting Started

Add an image to your prompt using the `withPrompt` method with an `Image` value object:

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\ValueObjects\Media\Image;

// From a local path
$response = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022')
    ->withPrompt(
        "What's in this image?",
        [Image::fromLocalPath(path: '/path/to/image.jpg')]
    )
    ->asText();

// From a path on a storage disk
$response = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022')
    ->withPrompt(
        "What's in this image?",
        [Image::fromStoragePath(
            path: '/path/to/image.jpg',
            diskName: 'my-disk' // optional - omit/null for default disk
        )]
    )
    ->asText();

// From a URL
$response = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022')
    ->withPrompt(
        'Analyze this diagram:',
        [Image::fromUrl(url: 'https://example.com/diagram.png')]
    )
    ->asText();

// From base64
$response = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022')
    ->withPrompt(
        'Analyze this diagram:',
        [Image::fromBase64(base64: base64_encode(file_get_contents('/path/to/image.jpg')))]
    )
    ->asText();

// From raw content
$response = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022')
    ->withPrompt(
        'Analyze this diagram:',
        [Image::fromRawContent(rawContent: file_get_contents('/path/to/image.jpg'))]
    )
    ->asText();
```

## Alternative: Using withMessages

Include images using the message-based approach:

```php
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Media\Image;

$message = new UserMessage(
    "What's in this image?",
    [Image::fromLocalPath(path: '/path/to/image.jpg')]
);

$response = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022')
    ->withMessages([$message])
    ->asText();
```

## Customizing Image Filenames

Provide a custom filename using the fluent `as()` method:

```php
$response = Prism::image()
    ->using('openai', 'gpt-image-1')
    ->withPrompt('Edit these images', [
        Image::fromLocalPath('path/to/photo1.png')->as('original-photo.png'),
        Image::fromLocalPath('path/to/photo2.png')->as('reference-image.png'),
    ])
    ->generate();
```

Without custom filenames, images are automatically named using a default pattern.

## Transfer Mediums

Providers are not consistent in their support of sending raw contents, base64 and/or URLs.

### Supported Conversions

- Where a provider does not support URLs: Prism will fetch the URL and use base64 or rawContent.
- Where you provide a file, base64 or rawContent: Prism will switch between base64 and rawContent depending on what the provider accepts.

### Limitations

- Where a provider only supports URLs: if you provide a file path, raw contents or base64, for security reasons Prism does not create a URL for you and your request will fail.
