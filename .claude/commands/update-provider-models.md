# Update Provider Models

Update the `app/AI/Enums/ProviderModel.php` enum with the latest models from all AI providers.

## Instructions

1. Use firecrawl to scrape each provider's model documentation page listed below
2. Extract the current model list from each provider. Additional scraping / crawling on a provider's site may be needed to get all model details.
3. Update the ProviderModel enum:
   - Adhere to instructions in the enum's docblock
   - Add new models that don't exist
   - Remove deprecated/discontinued models
   - Preserve existing models that are still valid
   - Update descriptions if they've changed
4. Maintain the naming conventions and grouping rules defined in the enum's docblock

## Provider Documentation URLs

Scrape these URLs to get the latest models for each provider:

| Provider | Documentation URL |
|----------|-------------------|
| OpenAI | https://platform.openai.com/docs/models |
| Anthropic | https://platform.claude.com/docs/en/about-claude/models/overview |
| Google Gemini | https://ai.google.dev/gemini-api/docs/models |
| Mistral | https://docs.mistral.ai/getting-started/models/ |
| Ollama | https://ollama.com/library |
| Groq | https://console.groq.com/docs/models |
| DeepSeek | https://api-docs.deepseek.com/quick_start/pricing |
| xAI | https://docs.x.ai/docs/models |
| Voyage AI | https://docs.voyageai.com/docs/embeddings |
| ElevenLabs | https://elevenlabs.io/docs/api-reference/speech-to-text |
| OpenRouter | https://openrouter.ai/models |

## Naming Convention

Enum case names follow this pattern:
```
{PROVIDER}_{TYPE}_{MODEL_NAME_NORMALIZED}
```

- **Provider**: OPENAI, ANTHROPIC, GEMINI, MISTRAL, OLLAMA, GROQ, DEEPSEEK, XAI, VOYAGEAI, ELEVENLABS, OPENROUTER
- **Type**: TEXT, IMAGE, AUDIO, EMBEDDING, MODERATION
- **Model Name**: Normalized to uppercase with underscores (e.g., `gpt-4o` becomes `GPT_4O`)

Examples:
- `OPENAI_TEXT_GPT_4O` for `gpt-4o`
- `ANTHROPIC_TEXT_CLAUDE_3_5_SONNET_LATEST` for `claude-3-5-sonnet-latest`
- `GEMINI_IMAGE_IMAGEN_4_0` for `imagen-4.0-generate-001`

## Ordering Rules

1. Group enum cases by provider (in order: OpenAI, Anthropic, Gemini, Mistral, Ollama, Groq, DeepSeek, xAI, VoyageAI, ElevenLabs, OpenRouter)
2. Within each provider, group by type (TEXT first, then IMAGE, AUDIO, EMBEDDING, MODERATION)
3. Within each type grouping, place newer models at the top

## Model Type Classification

- **TEXT**: Chat/completion models (GPT, Claude, Gemini, Llama, etc.)
- **IMAGE**: Image generation models (DALL-E, Imagen, Stable Diffusion)
- **AUDIO**: Speech-to-text and text-to-speech models (Whisper, TTS, Voxtral)
- **EMBEDDING**: Vector embedding models (text-embedding, voyage)
- **MODERATION**: Content moderation models

## Required Fields for Each Model

Each enum case must have:
1. A docblock comment describing the model
2. The string value matching the exact model ID used by the provider's API

Example:
```php
/** GPT-4o - Optimized GPT-4 model for speed and quality */
case OPENAI_TEXT_GPT_4O = 'gpt-4o';
```

## After Updating

1. Run `./vendor/bin/sail composer lint-fix` to fix code style
2. Run `./vendor/bin/sail composer analyse` to check for errors
3. Test the enum in tinker:
   ```php
   use App\AI\Enums\ProviderModel;
   use Prism\Prism\Enums\Provider;

   ProviderModel::cases();
   ProviderModel::forProvider(Provider::OpenAI);
   ProviderModel::textModels();
   ```
