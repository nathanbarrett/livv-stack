# Documents (Input Modality)

Prism supports including documents in your messages with some providers.

See the [provider support table](../INDEX.md) to check whether Prism supports your chosen provider.

> **NOTE:** Provider support may differ by model. Check your provider's documentation if you receive error messages.

## Supported File Types

> **TIP:** If provider interoperability is important to your app, we recommend converting documents to markdown.

Please check provider documentation for supported file/mime types, as support differs widely. The most supported file types are PDF and text/plain (which may include markdown).

## Transfer Mediums

> **TIP:** If provider interoperability is important, we recommend using rawContent or base64.

Providers are not consistent in their support of sending file raw contents, base64 and/or URLs.

### Supported Conversions

- Where a provider does not support URLs: Prism will fetch the URL and use base64 or rawContent.
- Where you provide a file, base64 or rawContent: Prism will switch between base64 and rawContent depending on what the provider accepts.

### Limitations

- Where a provider only supports URLs: if you provide a file path, raw contents, base64 or chunks, for security reasons Prism does not create a URL for you and your request will fail.
- Chunks cannot be passed between providers, as they could be in different formats.

## Getting Started

Add a document to your prompt using the `withPrompt` method with a `Document` value object:

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Media\Document;

// From a local path
$response = Prism::text()
    ->using('my-provider', 'my-model')
    ->withPrompt(
        'Analyze this document',
        [Document::fromLocalPath(
            path: 'tests/Fixtures/test-pdf.pdf',
            title: 'My document title' // optional
        )]
    )
    ->asText();

// From a storage path
$response = Prism::text()
    ->using('my-provider', 'my-model')
    ->withPrompt(
        'Summarize this document',
        [Document::fromStoragePath(
            path: 'mystoragepath/file.pdf',
            diskName: 'my-disk', // optional - omit/null for default disk
            title: 'My document title' // optional
        )]
    )
    ->asText();

// From base64
$response = Prism::text()
    ->using('my-provider', 'my-model')
    ->withPrompt(
        'Extract key points from this document',
        [Document::fromBase64(
            base64: $baseFromDB,
            mimeType: 'optional/mimetype', // optional
            title: 'My document title' // optional
        )]
    )
    ->asText();

// From raw content
$response = Prism::text()
    ->using('my-provider', 'my-model')
    ->withPrompt(
        'Review this document',
        [Document::fromRawContent(
            rawContent: $rawContent,
            mimeType: 'optional/mimetype', // optional
            title: 'My document title' // optional
        )]
    )
    ->asText();

// From a text string
$response = Prism::text()
    ->using('my-provider', 'my-model')
    ->withPrompt(
        'Process this text document',
        [Document::fromText(
            text: 'Hello world!',
            title: 'My document title' // optional
        )]
    )
    ->asText();

// From an URL
$response = Prism::text()
    ->using('my-provider', 'my-model')
    ->withPrompt(
        'Analyze this document from URL',
        [Document::fromUrl(
            url: 'https://example.com/test-pdf.pdf',
            title: 'My document title' // optional
        )]
    )
    ->asText();

// From chunks
$response = Prism::text()
    ->using('my-provider', 'my-model')
    ->withPrompt(
        'Process this chunked document',
        [Document::fromChunks(
            chunks: [
                'chunk one',
                'chunk two'
            ],
            title: 'My document title' // optional
        )]
    )
    ->asText();

// From a provider file ID
$response = Prism::text()
    ->using('my-provider', 'my-model')
    ->withPrompt(
        'Analyze this document from provider file',
        [Document::fromFileId(
            fileId: 'my-provider-file-id'
        )]
    )
    ->asText();
```

## Alternative: Using withMessages

Include documents using the message-based approach:

```php
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Media\Document;

$message = new UserMessage(
    'Analyze this document',
    [Document::fromLocalPath(
        path: 'tests/Fixtures/test-pdf.pdf',
        title: 'My document title' // optional
    )]
);

$response = Prism::text()
    ->using('my-provider', 'my-model')
    ->withMessages([$message])
    ->asText();
```
