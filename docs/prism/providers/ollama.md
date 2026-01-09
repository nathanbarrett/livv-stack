# Ollama

## Configuration

```php
'ollama' => [
    'url' => env('OLLAMA_URL', 'http://localhost:11434/v1'),
],
```

## Ollama Options

Customize how the model is run via [options](https://github.com/ollama/ollama/blob/main/docs/modelfile.md#parameter):

```php
Prism::text()
  ->using(Provider::Ollama, 'gemma3:1b')
  ->withPrompt('Who are you?')
  ->withClientOptions(['timeout' => 60])
  ->withProviderOptions([
      'top_p' => 0.9,
      'num_ctx' => 4096,
  ])
```

> **Note:** Using `withProviderOptions` will override settings like `topP` and `temperature`

## Streaming

```php
return Prism::text()
    ->using('ollama', 'llama3.2')
    ->withPrompt(request('message'))
    ->withClientOptions(['timeout' => 120])
    ->asEventStreamResponse();
```

> **Tip:** Remember to increase the timeout for local models to prevent premature disconnection.

## Considerations

### Timeouts

Responses may time out depending on your configuration. Extend the client's timeout:

```php
Prism::text()
  ->using(Provider::Ollama, 'gemma3:1b')
  ->withPrompt('Who are you?')
  ->withClientOptions(['timeout' => 60])
```

### Structured Output

Ollama doesn't have native JSON mode or structured output like some providers. Prism implements a workaround:

- Instructions are automatically appended to your prompt to guide the model to output valid JSON
- If the response isn't valid JSON, Prism will raise a `PrismException`

## Limitations

### Image URL

Ollama does not support images using `Image::fromUrl()`.

### Tool Choice

Ollama does not currently support tool choice / required tools.
