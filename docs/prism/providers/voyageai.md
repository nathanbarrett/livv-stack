# Voyage AI

## Configuration

```php
'voyageai' => [
    'api_key' => env('VOYAGEAI_API_KEY', ''),
    'url' => env('VOYAGEAI_URL', 'https://api.voyageai.com/v1'),
],
```

## Provider-specific Options

### Input Type

By default, Voyage AI generates general purpose vectors. You can tailor vectors for specific tasks:

**For search / querying:**

```php
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

Prism::embeddings()
    ->using(Provider::VoyageAI, 'voyage-3-lite')
    ->fromInput('The food was delicious and the waiter...')
    ->withProviderOptions(['inputType' => 'query'])
    ->asEmbeddings();
```

**For document retrieval:**

```php
Prism::embeddings()
    ->using(Provider::VoyageAI, 'voyage-3-lite')
    ->fromInput('The food was delicious and the waiter...')
    ->withProviderOptions(['inputType' => 'document'])
    ->asEmbeddings();
```

### Truncation

By default, Voyage AI truncates inputs over the context length. Force an error instead:

```php
Prism::embeddings()
    ->using(Provider::VoyageAI, 'voyage-3-lite')
    ->fromInput('The food was delicious and the waiter...')
    ->withProviderOptions(['truncation' => false])
    ->asEmbeddings();
```
