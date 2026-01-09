# Streaming Output

Prism provides multiple ways to handle streaming AI responses, from simple Server-Sent Events to WebSocket broadcasting for real-time applications.

> **WARNING:** When using Laravel Telescope or other packages that intercept HTTP client events, they may consume the stream before Prism can emit events. Consider disabling such interceptors when using streaming.

## Quick Start

### Server-Sent Events (SSE)

The simplest way to stream AI responses to a web interface:

```php
Route::get('/chat', function () {
    return Prism::text()
        ->using('anthropic', 'claude-3-7-sonnet')
        ->withPrompt(request('message'))
        ->asEventStreamResponse();
});
```

```javascript
const eventSource = new EventSource('/chat');

eventSource.addEventListener('text_delta', (event) => {
    const data = JSON.parse(event.data);
    document.getElementById('output').textContent += data.delta;
});

eventSource.addEventListener('stream_end', (event) => {
    const data = JSON.parse(event.data);
    console.log('Stream ended:', data.finish_reason);
    eventSource.close();
});
```

### Vercel AI SDK Integration

For apps using Vercel's AI SDK:

```php
Route::post('/api/chat', function () {
    return Prism::text()
        ->using('openai', 'gpt-4')
        ->withPrompt(request('message'))
        ->asDataStreamResponse();
});
```

Client-side with the `useChat` hook (AI SDK 5.0):

```javascript
import { useChat } from '@ai-sdk/react';
import { useState } from 'react';

export default function Chat() {
    const [input, setInput] = useState('');
    const { messages, sendMessage, status } = useChat({
        transport: { api: '/api/chat' },
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (input.trim() && status === 'ready') {
            sendMessage(input);
            setInput('');
        }
    };

    return (
        <div>
            <div>
                {messages.map(m => (
                    <div key={m.id}>
                        <strong>{m.role}:</strong>{' '}
                        {m.parts.filter(part => part.type === 'text').map(part => part.text).join('')}
                    </div>
                ))}
            </div>
            <form onSubmit={handleSubmit}>
                <input value={input} onChange={(e) => setInput(e.target.value)} />
                <button type="submit">Send</button>
            </form>
        </div>
    );
}
```

### WebSocket Broadcasting with Background Jobs

For real-time multi-user applications:

```php
// Job Class
namespace App\Jobs;

use Illuminate\Broadcasting\Channel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Prism\Prism\Facades\Prism;

class ProcessAiStreamJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $message,
        public string $channel,
        public string $model = 'claude-3-7-sonnet'
    ) {}

    public function handle(): void
    {
        Prism::text()
            ->using('anthropic', $this->model)
            ->withPrompt($this->message)
            ->asBroadcast(new Channel($this->channel));
    }
}
```

## Event Types

All streaming approaches emit the same core events:

| Event | Description |
|-------|-------------|
| `stream_start` | Stream initialization with model and provider info |
| `text_start` | Beginning of a text message |
| `text_delta` | Incremental text chunks as they're generated |
| `text_complete` | End of a complete text message |
| `thinking_start` | Beginning of AI reasoning/thinking session |
| `thinking_delta` | Reasoning content as it's generated |
| `thinking_complete` | End of reasoning session |
| `tool_call` | Tool invocation with arguments |
| `tool_result` | Tool execution results |
| `tool_call_delta` | Incremental tool call params chunks |
| `artifact` | Binary artifacts produced by tools |
| `provider_tool_event` | Provider-specific tool events |
| `error` | Error handling with recovery information |
| `stream_end` | Stream completion with usage statistics |

### Event Data Examples

```javascript
// text_delta event
{
    "id": "anthropic_evt_NbS3LIP0QDl5whYu",
    "timestamp": 1756412888,
    "delta": "Hello there! You want to know",
    "message_id": "msg_01BS7MKgXvUESY8yAEugphV2"
}

// tool_call event
{
    "id": "anthropic_evt_qXvozT6OqtmFPgkG",
    "timestamp": 1756412889,
    "tool_id": "toolu_01NAbzpjGxv2mJ8gJRX5Bb8m",
    "tool_name": "search",
    "arguments": {"query": "current date in Detroit"},
    "message_id": "msg_01BS7MKgXvUESY8yAEugphV2"
}

// stream_end event
{
    "id": "anthropic_evt_BZ3rqDYyprnywNyL",
    "timestamp": 1756412898,
    "finish_reason": "Stop",
    "usage": {
        "prompt_tokens": 3448,
        "completion_tokens": 192
    }
}
```

## Handling Artifact Events

When tools produce binary artifacts (images, audio, files), they're emitted as `ArtifactEvent`:

```javascript
// SSE
eventSource.addEventListener('artifact', (event) => {
    const data = JSON.parse(event.data);
    if (data.artifact.mime_type.startsWith('image/')) {
        const img = document.createElement('img');
        img.src = `data:${data.artifact.mime_type};base64,${data.artifact.data}`;
        document.getElementById('artifacts').appendChild(img);
    }
});
```

### Persisting Artifacts in Callbacks

```php
use Prism\Prism\Streaming\Events\ArtifactEvent;

return Prism::text()
    ->using('anthropic', 'claude-3-7-sonnet')
    ->withTools([$imageGeneratorTool])
    ->withPrompt(request('message'))
    ->asDataStreamResponse(function ($request, $events) use ($conversationId) {
        $events
            ->filter(fn ($event) => $event instanceof ArtifactEvent)
            ->each(function (ArtifactEvent $event) use ($conversationId) {
                Attachment::create([
                    'conversation_id' => $conversationId,
                    'tool_call_id' => $event->toolCallId,
                    'mime_type' => $event->artifact->mimeType,
                    'data' => $event->artifact->rawContent(),
                ]);
            });
    });
```

## Handling Completion with Callbacks

### Text Generation Callbacks

```php
use Prism\Prism\Text\PendingRequest;
use Prism\Prism\Text\Response;

$response = Prism::text()
    ->using('anthropic', 'claude-3-7-sonnet')
    ->withPrompt(request('message'))
    ->asText(function (PendingRequest $request, Response $response) use ($conversationId) {
        ConversationMessage::create([
            'conversation_id' => $conversationId,
            'role' => 'assistant',
            'content' => $response->text,
        ]);
    });
```

### Streaming Response Callbacks

```php
use Illuminate\Support\Collection;
use Prism\Prism\Streaming\Events\TextDeltaEvent;

return Prism::text()
    ->using('anthropic', 'claude-3-7-sonnet')
    ->withPrompt(request('message'))
    ->asEventStreamResponse(function ($request, Collection $events) use ($conversationId) {
        $fullText = $events
            ->filter(fn ($event) => $event instanceof TextDeltaEvent)
            ->map(fn ($event) => $event->delta)
            ->join('');

        ConversationMessage::create([
            'conversation_id' => $conversationId,
            'role' => 'assistant',
            'content' => $fullText,
        ]);
    });
```

### Using Invokable Classes

```php
class SaveStreamedConversation
{
    public function __construct(protected string $conversationId) {}

    public function __invoke($request, Collection $events): void
    {
        $fullText = $events
            ->filter(fn ($event) => $event instanceof TextDeltaEvent)
            ->map(fn ($event) => $event->delta)
            ->join('');

        ConversationMessage::create([
            'conversation_id' => $this->conversationId,
            'role' => 'assistant',
            'content' => $fullText,
        ]);
    }
}

return Prism::text()
    ->using('anthropic', 'claude-3-7-sonnet')
    ->withPrompt($message)
    ->asEventStreamResponse(new SaveStreamedConversation($conversationId));
```

## Custom Event Processing

Access raw events for complete control:

```php
$events = Prism::text()
    ->using('openai', 'gpt-4')
    ->withPrompt('Explain quantum physics')
    ->asStream();

foreach ($events as $event) {
    match ($event->type()) {
        StreamEventType::TextDelta => handleTextChunk($event),
        StreamEventType::ToolCall => handleToolCall($event),
        StreamEventType::StreamEnd => handleCompletion($event),
        default => null,
    };
}
```

## Streaming with Tools

```php
use Prism\Prism\Facades\Tool;

$searchTool = Tool::as('search')
    ->for('Search for information')
    ->withStringParameter('query', 'Search query')
    ->using(function (string $query) {
        return "Search results for: {$query}";
    });

return Prism::text()
    ->using('anthropic', 'claude-3-7-sonnet')
    ->withTools([$searchTool])
    ->withPrompt("What's the weather in Detroit?")
    ->asEventStreamResponse();
```
