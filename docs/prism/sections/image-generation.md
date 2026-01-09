# Image Generation

Generate stunning images from text prompts using AI-powered models. Prism provides a clean, consistent API for image generation across different providers.

## Getting Started

```php
use Prism\Prism\Facades\Prism;

$response = Prism::image()
    ->using('openai', 'dall-e-3')
    ->withPrompt('A cute baby sea otter floating on its back in calm blue water')
    ->generate();

$image = $response->firstImage();
echo $image->url;
```

## Provider Support

Currently supported:

- **OpenAI**: DALL-E 2, DALL-E 3, and GPT-Image-1 models
- **Gemini**: Gemini 2.0 Flash Preview Image Generation, Imagen 4, Imagen 3

## Basic Usage

### Simple Generation

```php
$response = Prism::image()
    ->using('openai', 'dall-e-3')
    ->withPrompt('A serene mountain landscape at sunset')
    ->generate();

$image = $response->firstImage();
if ($image->hasUrl()) {
    echo "Image URL: " . $image->url;
}
if ($image->hasBase64()) {
    echo "Base64 Image Data: " . $image->base64;
}
```

### Working with Responses

```php
$response = Prism::image()
    ->using('openai', 'dall-e-2')
    ->withPrompt('Abstract geometric patterns in vibrant colors')
    ->generate();

// Check if images were generated
if ($response->hasImages()) {
    echo "Generated {$response->imageCount()} image(s)";

    // Access all images
    foreach ($response->images as $image) {
        if ($image->hasUrl()) {
            echo "Image: {$image->url}\n";
        }

        if ($image->hasBase64()) {
            echo "Base64 Image: " . substr($image->base64, 0, 50) . "...\n";
        }

        if ($image->hasRevisedPrompt()) {
            echo "Revised prompt: {$image->revisedPrompt}\n";
        }
    }

    // Or just get the first one
    $firstImage = $response->firstImage();
}

// Check usage information
echo "Prompt tokens: {$response->usage->promptTokens}";
echo "Model used: {$response->meta->model}";
```

## Provider-Specific Options

### OpenAI Options

#### DALL-E 3 Options

```php
$response = Prism::image()
    ->using('openai', 'dall-e-3')
    ->withPrompt('A beautiful sunset over mountains')
    ->withProviderOptions([
        'size' => '1792x1024',          // 1024x1024, 1024x1792, 1792x1024
        'quality' => 'hd',              // standard, hd
        'style' => 'vivid',             // vivid, natural
        'response_format' => 'url',     // url, b64_json
    ])
    ->generate();
```

#### GPT-Image-1 (Base64 Only)

The GPT-Image-1 model always returns base64-encoded images:

```php
$response = Prism::image()
    ->using('openai', 'gpt-image-1')
    ->withPrompt('A cute baby sea otter floating on its back')
    ->withProviderOptions([
        'size' => '1024x1024',              // 1024x1024, 1536x1024, 1024x1536, auto
        'quality' => 'high',                // auto, high, medium, low
        'background' => 'transparent',      // transparent, opaque, auto
        'output_format' => 'png',           // png, jpeg, webp
        'output_compression' => 90,         // 0-100 (for jpeg/webp)
    ])
    ->generate();

$image = $response->firstImage();
if ($image->hasBase64()) {
    file_put_contents('generated-image.png', base64_decode($image->base64));
}
```

#### Image Editing

OpenAI's `gpt-image-1` model supports editing existing images:

```php
use Prism\Prism\ValueObjects\Media\Image;

$originalImage = Image::fromLocalPath('photos/landscape.png');

$response = Prism::image()
    ->using('openai', 'gpt-image-1')
    ->withPrompt('Add a vaporwave sunset to the background', [$originalImage])
    ->withProviderOptions([
        'size' => '1024x1024',
        'output_format' => 'png',
        'quality' => 'high',
    ])
    ->generate();

$editedImage = $response->firstImage();
file_put_contents('edited-landscape.png', base64_decode($editedImage->base64));
```

Edit multiple images:

```php
$response = Prism::image()
    ->using('openai', 'gpt-image-1')
    ->withPrompt('Make the colors more vibrant', [
        Image::fromLocalPath('photo1.png'),
        Image::fromLocalPath('photo2.png')->as('custom-name.png'),
    ])
    ->generate();
```

Using a mask for precise edits:

```php
$response = Prism::image()
    ->using('openai', 'gpt-image-1')
    ->withPrompt('Replace the sky with a starry night', [
        Image::fromLocalPath('landscape.png'),
    ])
    ->withProviderOptions([
        'mask' => Image::fromLocalPath('sky-mask.png'), // White areas will be edited
        'size' => '1024x1024',
        'output_format' => 'png',
    ])
    ->generate();
```

> **NOTE:** The mask should be a PNG where white pixels indicate areas to edit and transparent pixels indicate areas to preserve.

### Gemini Options

All Gemini image generation models return base64-encoded images.

#### Gemini Flash Preview Image Generation

Supports editing images by passing them as the second parameter:

```php
use Prism\Prism\ValueObjects\Media\Image;
use Prism\Prism\Enums\Provider;

$originalImage = Image::fromLocalPath('image/boots.png');

$response = Prism::image()
    ->using(Provider::Gemini, 'gemini-2.0-flash-preview-image-generation')
    ->withPrompt('Actually, could we make those boots red?', [$originalImage])
    ->generate();
```

#### Imagen Options

```php
$response = Prism::image()
    ->using(Provider::Gemini, 'imagen-4.0-generate-001')
    ->withPrompt('Generate an image of a magnificent building falling into the ocean')
    ->withProviderOptions([
        'n' => 3,                               // number of images to generate
        'size' => '2K',                         // 1K (default), 2K
        'aspect_ratio' => '16:9',               // 1:1 (default), 3:4, 4:3, 9:16, 16:9
        'person_generation' => 'dont_allow',    // dont_allow, allow_adult, allow_all
    ])
    ->generate();
```

## Testing

```php
use Prism\Prism\Testing\PrismFake;

test('can generate images', function () {
    $fake = PrismFake::create()->image();
    Prism::fake($fake);

    $response = Prism::image()
        ->using('openai', 'dall-e-3')
        ->withPrompt('Test image')
        ->generate();

    expect($response->hasImages())->toBeTrue();
    expect($response->firstImage()->url)->toContain('fake-image-url');
});
```
