# Moderation

Moderate content by checking it against AI-powered models. Moderation helps you detect potentially harmful or inappropriate content before it reaches your users or models.

## Quick Start

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

$response = Prism::moderation()
    ->using(Provider::OpenAI)
    ->withInput('Your text to check goes here')
    ->asModeration();

// Check if any content was flagged
if ($response->isFlagged()) {
    $flagged = $response->firstFlagged();
}
```

## Checking Multiple Inputs

```php
$response = Prism::moderation()
    ->using(Provider::OpenAI)
    // Multiple inputs as variadic arguments
    ->withInput('First text to check', 'Second text to check', 'Third text')
    ->asModeration();

// Or pass an array
$response = Prism::moderation()
    ->using(Provider::OpenAI)
    ->withInput(['First text', 'Second text', 'Third text'])
    ->asModeration();

// Get all flagged results
$flaggedResults = $response->flagged();

foreach ($flaggedResults as $result) {
    $categories = $result->categories;
    $scores = $result->categoryScores;
}
```

## Image Moderation

Check user-uploaded images for inappropriate content:

```php
use Prism\Prism\ValueObjects\Media\Image;

$response = Prism::moderation()
    ->using(Provider::OpenAI, 'omni-moderation-latest')
    ->withInput(Image::fromUrl('https://example.com/image.png'))
    ->asModeration();

if ($response->isFlagged()) {
    // Handle flagged image
}
```

### Mixed Text and Image Moderation

Check both text and images in a single request:

```php
$response = Prism::moderation()
    ->using(Provider::OpenAI, 'omni-moderation-latest')
    ->withInput(
        'Check this text',
        Image::fromUrl('https://example.com/image.png'),
        'Another text to check',
        Image::fromLocalPath('/path/to/image1.jpg')
    )
    ->asModeration();
```

> **IMPORTANT:** When mixing text and images, text inputs are treated as context/descriptions for the images, not as separate moderation inputs. If you need separate moderation results for text and images, make separate API calls.

## Input Methods

### Using withInput()

The unified way to add any type of input:

```php
// Single text input
$response = Prism::moderation()
    ->using(Provider::OpenAI)
    ->withInput('Check this text for moderation')
    ->asModeration();

// Multiple text inputs
$response = Prism::moderation()
    ->using(Provider::OpenAI)
    ->withInput('Text 1', 'Text 2', 'Text 3')
    ->asModeration();

// Single image
$response = Prism::moderation()
    ->using(Provider::OpenAI, 'omni-moderation-latest')
    ->withInput(Image::fromUrl('https://example.com/image.png'))
    ->asModeration();

// Multiple images
$response = Prism::moderation()
    ->using(Provider::OpenAI, 'omni-moderation-latest')
    ->withInput([
        Image::fromUrl('https://example.com/image1.png'),
        Image::fromLocalPath('/path/to/image2.jpg'),
    ])
    ->asModeration();
```

### Image Sources

```php
use Prism\Prism\ValueObjects\Media\Image;

Image::fromUrl('https://example.com/image.png');
Image::fromLocalPath('/path/to/image.jpg');
Image::fromStoragePath('/path/to/image.jpg', 'my-disk');
Image::fromBase64($base64Data, 'image/jpeg');
```

> **NOTE:** Image moderation requires the `omni-moderation-latest` model (or similar image-capable moderation models).

## Response Handling

```php
use Prism\Prism\ValueObjects\ModerationResult;

// Check if any content was flagged
if ($response->isFlagged()) {
    // Get the first flagged result
    $firstFlagged = $response->firstFlagged();

    // Or get all flagged results
    $allFlagged = $response->flagged();
}

// Access individual results
foreach ($response->results as $result) {
    /** @var ModerationResult $result */
    $isFlagged = $result->flagged;
    $categories = $result->categories;       // Array of category => bool
    $categoryScores = $result->categoryScores; // Array of category => float
}

// Access response metadata
$meta = $response->meta;
$model = $meta->model;
$id = $meta->id;
$rateLimits = $meta->rateLimits;
```

### Understanding Results

Each moderation result includes:

- **`flagged`**: Boolean indicating if content was flagged as potentially harmful
- **`categories`**: Array mapping category names to boolean values
- **`categoryScores`**: Array mapping category names to float confidence levels

```php
$result = $response->results[0];

if ($result->flagged) {
    // Check specific categories
    if ($result->categories['hate'] ?? false) {
        // Handle hate content
    }

    if ($result->categories['harassment'] ?? false) {
        // Handle harassment
    }

    // Check scores for more nuanced handling
    $hateScore = $result->categoryScores['hate'] ?? 0.0;
    if ($hateScore > 0.5) {
        // High confidence of hate content
    }
}
```

## Common Settings

```php
$response = Prism::moderation()
    ->using(Provider::OpenAI, 'omni-moderation-latest')
    ->withInput('Your text here')
    ->withClientOptions(['timeout' => 30])
    ->withClientRetry(3, 100)
    ->withProviderOptions([
        // Provider-specific options
    ])
    ->asModeration();
```

## Error Handling

```php
use Prism\Prism\Exceptions\PrismException;

try {
    $response = Prism::moderation()
        ->using(Provider::OpenAI)
        ->withInput('Your text here')
        ->asModeration();

    if ($response->isFlagged()) {
        // Handle flagged content
    }
} catch (PrismException $e) {
    Log::error('Moderation check failed:', [
        'error' => $e->getMessage()
    ]);
}
```

## Use Cases

- **User-Generated Content**: Check comments, posts, or messages before displaying
- **Content Filtering**: Filter out inappropriate content in chat applications
- **Image Moderation**: Verify user-uploaded images meet platform guidelines
- **Pre-Processing**: Check inputs before sending them to other AI models
- **Compliance**: Ensure content meets platform guidelines and policies
- **Mixed Content**: Check both text and images together in a single request

## Pro Tips

**Thresholds**: Use category scores to implement custom thresholds. Different applications may need different sensitivity levels.

**Batch Processing**: Check multiple inputs in a single request for better performance.

**Caching**: Consider caching moderation results for repeated content to reduce API calls.

**Logging**: Always log flagged content for audit trails and to improve filtering over time.

> **IMPORTANT:** Different providers may have different category names and scoring systems. Always check your provider's documentation for specifics.
