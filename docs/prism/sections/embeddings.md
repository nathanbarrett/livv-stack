# Embeddings

Transform your content into powerful vector representations! Embeddings let you add semantic search, recommendation systems, and other advanced features to your applications.

## Quick Start

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

$response = Prism::embeddings()
    ->using(Provider::OpenAI, 'text-embedding-3-large')
    ->fromInput('Your text goes here')
    ->asEmbeddings();

// Get your embeddings vector
$embeddings = $response->embeddings[0]->embedding;

// Check token usage
echo $response->usage->tokens;
```

## Generating Multiple Embeddings

Generate multiple embeddings at once (not supported by Gemini):

```php
$response = Prism::embeddings()
    ->using(Provider::OpenAI, 'text-embedding-3-large')
    ->fromInput('Your text goes here')
    ->fromInput('Your second text goes here')
    ->fromArray([
        'Third',
        'Fourth'
    ])
    ->asEmbeddings();

foreach ($response->embeddings as $embedding) {
    $embedding->embedding; // The vector
}

echo $response->usage->tokens;
```

## Input Methods

### Direct Text Input

```php
$response = Prism::embeddings()
    ->using(Provider::OpenAI, 'text-embedding-3-large')
    ->fromInput('Analyze this text')
    ->asEmbeddings();
```

### From File

```php
$response = Prism::embeddings()
    ->using(Provider::OpenAI, 'text-embedding-3-large')
    ->fromFile('/path/to/your/document.txt')
    ->asEmbeddings();
```

> **NOTE:** Make sure your file exists and is readable. The generator will throw a `PrismException` if there's any issue accessing the file.

## Image Embeddings

Some providers support image embeddings for visual similarity search and multimodal applications:

> **IMPORTANT:** Image embeddings require a provider and model that supports image input (like CLIP-based models or multimodal embedding models).

### Single Image

```php
use Prism\Prism\ValueObjects\Media\Image;

$response = Prism::embeddings()
    ->using('provider', 'model')
    ->fromImage(Image::fromLocalPath('/path/to/product.jpg'))
    ->asEmbeddings();

$embedding = $response->embeddings[0]->embedding;
```

### Multiple Images

```php
$response = Prism::embeddings()
    ->using('provider', 'model')
    ->fromImages([
        Image::fromLocalPath('/path/to/image1.jpg'),
        Image::fromUrl('https://example.com/image2.png'),
    ])
    ->asEmbeddings();

foreach ($response->embeddings as $embedding) {
    $vector = $embedding->embedding;
}
```

### Multimodal: Text + Image

Combine text and images for cross-modal search scenarios:

```php
$response = Prism::embeddings()
    ->using('provider', 'model')
    ->fromInput('Find similar products in red')
    ->fromImage(Image::fromBase64($productImage, 'image/png'))
    ->asEmbeddings();
```

> **TIP:** The `Image` class supports: `fromLocalPath()`, `fromUrl()`, `fromBase64()`, `fromStoragePath()`, and `fromRawContent()`.

## Common Settings

```php
$response = Prism::embeddings()
    ->using(Provider::OpenAI, 'text-embedding-3-large')
    ->fromInput('Your text here')
    ->withClientOptions(['timeout' => 30])
    ->withClientRetry(3, 100)
    ->asEmbeddings();
```

## Response Handling

```php
// Get an array of Embedding value objects
$embeddings = $response->embeddings;

// Just get first embedding
$firstVectorSet = $embeddings[0]->embedding;

// Loop over all embeddings
foreach ($embeddings as $embedding) {
    $vectorSet = $embedding->embedding;
}

// Check token usage
$tokenCount = $response->usage->tokens;
```

## Error Handling

```php
use Prism\Prism\Exceptions\PrismException;

try {
    $response = Prism::embeddings()
        ->using(Provider::OpenAI, 'text-embedding-3-large')
        ->fromInput('Your text here')
        ->asEmbeddings();
} catch (PrismException $e) {
    Log::error('Embeddings generation failed:', [
        'error' => $e->getMessage()
    ]);
}
```

## Pro Tips

**Vector Storage**: Consider using a vector database like Milvus, Qdrant, or pgvector to store and query your embeddings efficiently.

**Text Preprocessing**: For best results, clean and normalize your text before generating embeddings:
- Removing unnecessary whitespace
- Converting to lowercase
- Removing special characters
- Handling Unicode normalization

> **IMPORTANT:** Different providers and models produce vectors of different dimensions. Always check your provider's documentation for specific details about the embedding model you're using.
