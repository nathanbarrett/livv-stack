# Prism PHP Documentation

Prism is a powerful Laravel package for integrating Large Language Models (LLMs) into your applications. It provides a unified interface to work with various AI providers including OpenAI, Anthropic, Gemini, Mistral, Ollama, and more, allowing you to focus on building innovative AI features without getting bogged down in API implementation details.

**Official Documentation:** [https://prismphp.com](https://prismphp.com)

## Table of Contents

### Getting Started
- [Installation](sections/installation.md) - Requirements and setup instructions for adding Prism to your Laravel project
- [Configuration](sections/configuration.md) - Provider configuration, environment variables, and runtime overrides

### Core Concepts
- [Text Generation](sections/text-generation.md) - Basic and advanced text generation with LLMs, including multi-modal input and conversations
- [Structured Output](sections/structured-output.md) - Get AI responses as structured data using schemas
- [Tools & Function Calling](sections/tools-function-calling.md) - Extend AI capabilities with custom tools and function calling
- [Schemas](sections/schemas.md) - Define data structures for tools and structured output
- [Streaming Output](sections/streaming-output.md) - Real-time AI responses via SSE, WebSockets, and Vercel AI SDK
- [Embeddings](sections/embeddings.md) - Generate vector embeddings for semantic search and recommendations
- [Audio Processing](sections/audio-processing.md) - Text-to-speech and speech-to-text functionality
- [Image Generation](sections/image-generation.md) - Generate images from text prompts using AI models
- [Moderation](sections/moderation.md) - Content moderation for text and images
- [Testing](sections/testing.md) - Test your Prism integrations with fake responses
- [Prism Server](sections/prism-server.md) - Expose Prism models through an OpenAI-compatible API

### Input Modalities
- [Images](sections/input-images.md) - Include images in prompts for vision analysis
- [Documents](sections/input-documents.md) - Process PDFs and text documents with AI
- [Audio](sections/input-audio.md) - Include audio files in prompts for analysis
- [Video](sections/input-video.md) - Analyze video content including YouTube videos

### Advanced Topics
- [Custom Providers](sections/custom-providers.md) - Create and register your own AI providers
- [Provider Interoperability](sections/provider-interoperability.md) - Write provider-agnostic code with conditional configurations
- [Error Handling](sections/error-handling.md) - Handle exceptions and provider-specific errors
- [Rate Limits](sections/rate-limits.md) - Handle rate limiting and implement dynamic rate limiting

### Provider-Specific Documentation
- [Anthropic](providers/anthropic.md) - Claude models, prompt caching, extended thinking, citations
- [OpenAI](providers/openai.md) - GPT models, reasoning, DALL-E, Whisper, moderation
- [Gemini](providers/gemini.md) - Google AI models, search grounding, thinking mode, video/audio analysis
- [Ollama](providers/ollama.md) - Local models, configuration options, timeouts
- [Mistral](providers/mistral.md) - Reasoning models, Voxtral audio, OCR
- [DeepSeek](providers/deepseek.md) - DeepSeek models and limitations
- [xAI](providers/xai.md) - Grok models, extended thinking, structured output
- [Voyage AI](providers/voyageai.md) - Specialized embeddings provider
- [ElevenLabs](providers/elevenlabs.md) - Speech-to-text with diarization
- [Groq](providers/groq.md) - Ultra-fast LPU models, PlayAI TTS, Whisper STT

## Provider Support

Prism supports the following AI providers:

| Provider | Text | Streaming | Structured | Embeddings | Image | Speech-to-Text | Text-to-Speech | Tools | Documents | Moderation |
|----------|------|-----------|------------|------------|-------|----------------|----------------|-------|-----------|------------|
| Anthropic | ✓ | ✓ | ✓ | - | ✓ | - | - | ✓ | ✓ | - |
| OpenAI | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Gemini | ✓ | ✓ | ✓ | ✓ | ✓ | - | - | ✓ | ✓ | - |
| Mistral | ✓ | ✓ | ✓ | ✓ | - | ✓ | - | ✓ | ✓ | - |
| Ollama | ✓ | ✓ | ✓ | ✓ | ✓ | - | - | ✓ | - | - |
| Groq | ✓ | ✓ | ✓ | - | - | ✓ | ✓ | ✓ | - | - |
| DeepSeek | ✓ | ✓ | ✓ | - | - | - | - | ✓ | - | - |
| xAI | ✓ | ✓ | ✓ | - | - | - | - | ✓ | - | - |
| Voyage AI | - | - | - | ✓ | - | - | - | - | - | - |
| ElevenLabs | - | - | - | - | - | ✓ | - | - | - | - |

## Quick Example

```php
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

$response = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-latest')
    ->withSystemPrompt(view('prompts.system'))
    ->withPrompt('Explain quantum computing to a 5-year-old.')
    ->asText();

echo $response->text;
```

## Key Features

- **Unified Provider Interface**: Switch seamlessly between AI providers without changing your application code
- **Tool System**: Extend AI capabilities by defining custom tools that interact with your business logic
- **Multi-Modal Support**: Work with text, images, audio, video, and documents
- **Streaming**: Real-time responses via SSE, WebSockets, and Vercel AI SDK integration
- **Structured Output**: Get responses as typed PHP arrays using schemas
- **Embeddings**: Generate vector representations for semantic search
- **Audio Processing**: Text-to-speech and speech-to-text capabilities
- **Image Generation**: Create images from text prompts
- **Testing**: Powerful fake implementation for testing AI integrations
