# DeepSeek

## Configuration

```php
'deepseek' => [
    'api_key' => env('DEEPSEEK_API_KEY', ''),
    'url' => env('DEEPSEEK_URL', 'https://api.deepseek.com/v1')
]
```

## Streaming

DeepSeek supports streaming responses in real-time:

```php
return Prism::text()
    ->using('deepseek', 'deepseek-chat')
    ->withPrompt(request('message'))
    ->asEventStreamResponse();
```

## Limitations

### Embeddings

Does not support embeddings.

### Tool Choice

Does not support tool choice.

### Images

Does not support images.
