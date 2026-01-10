<?php

declare(strict_types=1);

namespace App\AI\Enums;

use Prism\Prism\Enums\Provider;

/**
 * AI Provider Models Enum
 *
 * Naming Convention: {PROVIDER}_{TYPE}_{MODEL_NAME}
 * - Provider: OPENAI, ANTHROPIC, GEMINI, MISTRAL, OLLAMA, GROQ, DEEPSEEK, XAI, VOYAGEAI, ELEVENLABS, OPENROUTER
 * - Type: TEXT, IMAGE, AUDIO, EMBEDDING, MODERATION
 *
 * All enum values MUST match what the provider expects in their API.
 *
 * Ordering Rules:
 * 1. Group by provider
 * 2. Within provider, group by type (TEXT first, then IMAGE, AUDIO, EMBEDDING, MODERATION)
 * 3. Newer models at top of their type grouping
 *
 * To update: Run /update-provider-models command
 *
 * @see Provider
 */
enum ProviderModel: string
{
    // ==========================================
    // OPENAI
    // ==========================================

    // OpenAI Text Models (Frontier)
    /** GPT-5.2 - Best model for coding and agentic tasks across industries */
    case OPENAI_TEXT_GPT_5_2 = 'gpt-5.2';

    /** GPT-5.2 Pro - Smarter and more precise version of GPT-5.2 */
    case OPENAI_TEXT_GPT_5_2_PRO = 'gpt-5.2-pro';

    /** GPT-5.1 - Best model for coding and agentic tasks with configurable reasoning */
    case OPENAI_TEXT_GPT_5_1 = 'gpt-5.1';

    /** GPT-5 - Intelligent reasoning model for coding and agentic tasks */
    case OPENAI_TEXT_GPT_5 = 'gpt-5';

    /** GPT-5 Pro - More compute for better responses */
    case OPENAI_TEXT_GPT_5_PRO = 'gpt-5-pro';

    /** GPT-5 Mini - Faster, cost-efficient version of GPT-5 */
    case OPENAI_TEXT_GPT_5_MINI = 'gpt-5-mini';

    /** GPT-5 Nano - Fastest, most cost-efficient version of GPT-5 */
    case OPENAI_TEXT_GPT_5_NANO = 'gpt-5-nano';

    /** GPT-4.1 - Smartest non-reasoning model */
    case OPENAI_TEXT_GPT_4_1 = 'gpt-4.1';

    /** GPT-4.1 Mini - Smaller, faster version of GPT-4.1 */
    case OPENAI_TEXT_GPT_4_1_MINI = 'gpt-4.1-mini';

    /** GPT-4.1 Nano - Fastest, most cost-efficient version of GPT-4.1 */
    case OPENAI_TEXT_GPT_4_1_NANO = 'gpt-4.1-nano';

    /** GPT-4o - Fast, intelligent, flexible GPT model */
    case OPENAI_TEXT_GPT_4O = 'gpt-4o';

    /** GPT-4o Mini - Fast, affordable small model for focused tasks */
    case OPENAI_TEXT_GPT_4O_MINI = 'gpt-4o-mini';

    /** o4-mini - Fast, cost-efficient reasoning model */
    case OPENAI_TEXT_O4_MINI = 'o4-mini';

    /** o3 - Reasoning model for complex tasks */
    case OPENAI_TEXT_O3 = 'o3';

    /** o3-pro - Version of o3 with more compute */
    case OPENAI_TEXT_O3_PRO = 'o3-pro';

    /** o3-mini - Small model alternative to o3 */
    case OPENAI_TEXT_O3_MINI = 'o3-mini';

    // OpenAI Codex Models
    /** GPT-5.1 Codex Max - Most intelligent coding model for long-horizon agentic tasks */
    case OPENAI_TEXT_GPT_5_1_CODEX_MAX = 'gpt-5.1-codex-max';

    /** GPT-5.1 Codex - Optimized for agentic coding in Codex */
    case OPENAI_TEXT_GPT_5_1_CODEX = 'gpt-5.1-codex';

    /** GPT-5.1 Codex Mini - Smaller, cost-effective coding model */
    case OPENAI_TEXT_GPT_5_1_CODEX_MINI = 'gpt-5.1-codex-mini';

    /** GPT-5 Codex - Optimized for agentic coding in Codex */
    case OPENAI_TEXT_GPT_5_CODEX = 'gpt-5-codex';

    // OpenAI Open-Weight Models
    /** GPT-OSS-120B - Most powerful open-weight model, fits into H100 GPU */
    case OPENAI_TEXT_GPT_OSS_120B = 'gpt-oss-120b';

    /** GPT-OSS-20B - Medium-sized open-weight model for low latency */
    case OPENAI_TEXT_GPT_OSS_20B = 'gpt-oss-20b';

    // OpenAI Specialized Models
    /** o3-deep-research - Most powerful deep research model */
    case OPENAI_TEXT_O3_DEEP_RESEARCH = 'o3-deep-research';

    /** o4-mini-deep-research - Faster, more affordable deep research model */
    case OPENAI_TEXT_O4_MINI_DEEP_RESEARCH = 'o4-mini-deep-research';

    /** Computer Use Preview - Specialized model for computer use tool */
    case OPENAI_TEXT_COMPUTER_USE_PREVIEW = 'computer-use-preview';

    // OpenAI Image Models
    /** GPT Image 1.5 - State-of-the-art image generation model */
    case OPENAI_IMAGE_GPT_IMAGE_1_5 = 'gpt-image-1.5';

    /** GPT Image 1 - Advanced image generation with editing capabilities */
    case OPENAI_IMAGE_GPT_IMAGE_1 = 'gpt-image-1';

    /** GPT Image 1 Mini - Cost-efficient version of GPT Image 1 */
    case OPENAI_IMAGE_GPT_IMAGE_1_MINI = 'gpt-image-1-mini';

    // OpenAI Video Models
    /** Sora 2 Pro - Most advanced synced-audio video generation */
    case OPENAI_VIDEO_SORA_2_PRO = 'sora-2-pro';

    /** Sora 2 - Flagship video generation with synced audio */
    case OPENAI_VIDEO_SORA_2 = 'sora-2';

    // OpenAI Audio Models
    /** GPT-4o Mini TTS - Text-to-speech model powered by GPT-4o mini */
    case OPENAI_AUDIO_GPT_4O_MINI_TTS = 'gpt-4o-mini-tts';

    /** GPT-4o Transcribe Diarize - Transcription with speaker identification */
    case OPENAI_AUDIO_GPT_4O_TRANSCRIBE_DIARIZE = 'gpt-4o-transcribe-diarize';

    /** GPT-4o Transcribe - Speech-to-text model powered by GPT-4o */
    case OPENAI_AUDIO_GPT_4O_TRANSCRIBE = 'gpt-4o-transcribe';

    /** GPT-4o Mini Transcribe - Speech-to-text model powered by GPT-4o mini */
    case OPENAI_AUDIO_GPT_4O_MINI_TRANSCRIBE = 'gpt-4o-mini-transcribe';

    /** TTS-1 HD - Text-to-speech model optimized for quality */
    case OPENAI_AUDIO_TTS_1_HD = 'tts-1-hd';

    /** TTS-1 - Text-to-speech model optimized for speed */
    case OPENAI_AUDIO_TTS_1 = 'tts-1';

    /** Whisper-1 - General-purpose speech recognition model */
    case OPENAI_AUDIO_WHISPER_1 = 'whisper-1';

    // OpenAI Realtime Models
    /** GPT Realtime - Realtime text and audio inputs/outputs */
    case OPENAI_AUDIO_GPT_REALTIME = 'gpt-realtime';

    /** GPT Audio - Audio inputs/outputs with Chat Completions API */
    case OPENAI_AUDIO_GPT_AUDIO = 'gpt-audio';

    /** GPT Realtime Mini - Cost-efficient version of GPT Realtime */
    case OPENAI_AUDIO_GPT_REALTIME_MINI = 'gpt-realtime-mini';

    /** GPT Audio Mini - Cost-efficient version of GPT Audio */
    case OPENAI_AUDIO_GPT_AUDIO_MINI = 'gpt-audio-mini';

    // OpenAI Embedding Models
    /** Text Embedding 3 Large - Most capable embedding model */
    case OPENAI_EMBEDDING_TEXT_EMBEDDING_3_LARGE = 'text-embedding-3-large';

    /** Text Embedding 3 Small - Small embedding model */
    case OPENAI_EMBEDDING_TEXT_EMBEDDING_3_SMALL = 'text-embedding-3-small';

    // OpenAI Moderation Models
    /** Omni Moderation Latest - Identifies harmful content in text and images */
    case OPENAI_MODERATION_OMNI_MODERATION_LATEST = 'omni-moderation-latest';

    // ==========================================
    // ANTHROPIC
    // ==========================================

    // Anthropic Text Models
    /** Claude Opus 4.5 - Premium model combining maximum intelligence with practical performance */
    case ANTHROPIC_TEXT_CLAUDE_OPUS_4_5 = 'claude-opus-4-5-20251101';

    /** Claude Sonnet 4.5 - Smart model for complex agents and coding */
    case ANTHROPIC_TEXT_CLAUDE_SONNET_4_5 = 'claude-sonnet-4-5-20250929';

    /** Claude Haiku 4.5 - Fastest model with near-frontier intelligence */
    case ANTHROPIC_TEXT_CLAUDE_HAIKU_4_5 = 'claude-haiku-4-5-20251001';

    // ==========================================
    // GEMINI
    // ==========================================

    // Gemini Text Models
    /** Gemini 3 Pro Preview - Most intelligent model for multimodal understanding and agentic tasks */
    case GEMINI_TEXT_GEMINI_3_PRO_PREVIEW = 'gemini-3-pro-preview';

    /** Gemini 3 Flash Preview - Most balanced model for speed, scale, and frontier intelligence */
    case GEMINI_TEXT_GEMINI_3_FLASH_PREVIEW = 'gemini-3-flash-preview';

    /** Gemini 2.5 Pro - State-of-the-art thinking model for complex problems */
    case GEMINI_TEXT_GEMINI_2_5_PRO = 'gemini-2.5-pro';

    /** Gemini 2.5 Flash - Best price-performance model for large scale processing */
    case GEMINI_TEXT_GEMINI_2_5_FLASH = 'gemini-2.5-flash';

    /** Gemini 2.5 Flash Lite - Fastest flash model optimized for cost-efficiency */
    case GEMINI_TEXT_GEMINI_2_5_FLASH_LITE = 'gemini-2.5-flash-lite';

    /** Gemini 2.0 Flash - Second generation workhorse model with 1M context */
    case GEMINI_TEXT_GEMINI_2_0_FLASH = 'gemini-2.0-flash';

    /** Gemini 2.0 Flash Lite - Second generation fast model optimized for cost */
    case GEMINI_TEXT_GEMINI_2_0_FLASH_LITE = 'gemini-2.0-flash-lite';

    // Gemini Image Models
    /** Gemini 3 Pro Image Preview - Image generation with Gemini 3 Pro */
    case GEMINI_IMAGE_GEMINI_3_PRO_IMAGE_PREVIEW = 'gemini-3-pro-image-preview';

    /** Gemini 2.5 Flash Image - Image generation with Gemini 2.5 Flash */
    case GEMINI_IMAGE_GEMINI_2_5_FLASH_IMAGE = 'gemini-2.5-flash-image';

    /** Gemini 2.0 Flash Preview Image Generation - Experimental image generation */
    case GEMINI_IMAGE_GEMINI_2_0_FLASH_PREVIEW_IMAGE_GENERATION = 'gemini-2.0-flash-preview-image-generation';

    // Gemini Audio Models
    /** Gemini 2.5 Pro Preview TTS - Text-to-speech with Gemini 2.5 Pro */
    case GEMINI_AUDIO_GEMINI_2_5_PRO_PREVIEW_TTS = 'gemini-2.5-pro-preview-tts';

    /** Gemini 2.5 Flash Preview TTS - Text-to-speech with Gemini 2.5 Flash */
    case GEMINI_AUDIO_GEMINI_2_5_FLASH_PREVIEW_TTS = 'gemini-2.5-flash-preview-tts';

    /** Gemini 2.5 Flash Native Audio Preview - Live audio with native audio support */
    case GEMINI_AUDIO_GEMINI_2_5_FLASH_NATIVE_AUDIO_PREVIEW = 'gemini-2.5-flash-native-audio-preview-12-2025';

    // Gemini Embedding Models
    /** Text Embedding 004 - Gemini's embedding model */
    case GEMINI_EMBEDDING_TEXT_EMBEDDING_004 = 'text-embedding-004';

    // ==========================================
    // MISTRAL
    // ==========================================

    // Mistral Text Models (Generalist)
    /** Mistral Large 3 - State-of-the-art open-weight general-purpose multimodal model */
    case MISTRAL_TEXT_MISTRAL_LARGE_3 = 'mistral-large-2512';

    /** Mistral Medium 3.1 - Frontier-class multimodal model */
    case MISTRAL_TEXT_MISTRAL_MEDIUM_3_1 = 'mistral-medium-2508';

    /** Mistral Small 3.2 - Updated small model */
    case MISTRAL_TEXT_MISTRAL_SMALL_3_2 = 'mistral-small-2506';

    /** Ministral 3 14B - Powerful model with best-in-class text and vision */
    case MISTRAL_TEXT_MINISTRAL_3_14B = 'ministral-3-14b-2512';

    /** Ministral 3 8B - Efficient model with best-in-class text and vision */
    case MISTRAL_TEXT_MINISTRAL_3_8B = 'ministral-3-8b-2512';

    /** Ministral 3 3B - Tiny and efficient model */
    case MISTRAL_TEXT_MINISTRAL_3_3B = 'ministral-3-3b-2512';

    // Mistral Reasoning Models
    /** Magistral Medium 1.2 - Frontier-class multimodal reasoning model */
    case MISTRAL_TEXT_MAGISTRAL_MEDIUM_1_2 = 'magistral-medium-2509';

    /** Magistral Small 1.2 - Small multimodal reasoning model */
    case MISTRAL_TEXT_MAGISTRAL_SMALL_1_2 = 'magistral-small-2509';

    // Mistral Specialist Models (Code)
    /** Devstral 2 - Frontier code agents model for SWE tasks */
    case MISTRAL_TEXT_DEVSTRAL_2 = 'devstral-2512';

    /** Codestral - Cutting-edge language model for code completion */
    case MISTRAL_TEXT_CODESTRAL = 'codestral-2508';

    /** Devstral Medium 1.0 - Enterprise grade text model for SWE use cases */
    case MISTRAL_TEXT_DEVSTRAL_MEDIUM_1_0 = 'devstral-medium-2507';

    /** Devstral Small 1.1 - Open source model for SWE use cases */
    case MISTRAL_TEXT_DEVSTRAL_SMALL_1_1 = 'devstral-small-2507';

    // Mistral OCR Models
    /** OCR 3 - OCR service for Document AI stack */
    case MISTRAL_TEXT_OCR_3 = 'mistral-ocr-2512';

    // Mistral Audio Models
    /** Voxtral Small - First model with audio input capabilities */
    case MISTRAL_AUDIO_VOXTRAL_SMALL = 'voxtral-small-2507';

    /** Voxtral Mini - Mini version of audio input model */
    case MISTRAL_AUDIO_VOXTRAL_MINI = 'voxtral-mini-2507';

    /** Voxtral Mini Transcribe - Efficient audio model for transcription */
    case MISTRAL_AUDIO_VOXTRAL_MINI_TRANSCRIBE = 'voxtral-mini-transcribe-2507';

    // Mistral Embedding Models
    /** Codestral Embed - State-of-the-art semantic for code extracts */
    case MISTRAL_EMBEDDING_CODESTRAL_EMBED = 'codestral-embed-2505';

    /** Mistral Embed - Embedding model for semantic search */
    case MISTRAL_EMBEDDING_MISTRAL_EMBED = 'mistral-embed';

    // Mistral Moderation Models
    /** Mistral Moderation - Moderation service for harmful text detection */
    case MISTRAL_MODERATION_MISTRAL_MODERATION = 'mistral-moderation-2411';

    // ==========================================
    // OLLAMA
    // ==========================================

    // Ollama Text Models (examples - varies by installation)
    /** Llama 3.2 - Meta's latest Llama model */
    case OLLAMA_TEXT_LLAMA_3_2 = 'llama3.2';

    /** Gemma 3 1B - Google's small efficient model */
    case OLLAMA_TEXT_GEMMA_3_1B = 'gemma3:1b';

    // ==========================================
    // GROQ
    // ==========================================

    // Groq Text Models (Production)
    /** Llama 3.3 70B Versatile - High-performance model on Groq LPU */
    case GROQ_TEXT_LLAMA_3_3_70B_VERSATILE = 'llama-3.3-70b-versatile';

    /** Llama 3.1 8B Instant - Fast, efficient model */
    case GROQ_TEXT_LLAMA_3_1_8B_INSTANT = 'llama-3.1-8b-instant';

    /** OpenAI GPT-OSS-120B - OpenAI's flagship open-weight model on Groq */
    case GROQ_TEXT_GPT_OSS_120B = 'openai/gpt-oss-120b';

    /** OpenAI GPT-OSS-20B - Medium open-weight model on Groq */
    case GROQ_TEXT_GPT_OSS_20B = 'openai/gpt-oss-20b';

    // Groq Text Models (Preview)
    /** Llama 4 Maverick 17B - Preview model with 128E */
    case GROQ_TEXT_LLAMA_4_MAVERICK_17B = 'meta-llama/llama-4-maverick-17b-128e-instruct';

    /** Llama 4 Scout 17B - Preview model with 16E */
    case GROQ_TEXT_LLAMA_4_SCOUT_17B = 'meta-llama/llama-4-scout-17b-16e-instruct';

    /** Moonshot Kimi K2 - High-performance model */
    case GROQ_TEXT_KIMI_K2 = 'moonshotai/kimi-k2-instruct-0905';

    /** Qwen3-32B - Alibaba Cloud model */
    case GROQ_TEXT_QWEN3_32B = 'qwen/qwen3-32b';

    // Groq Compound Systems
    /** Groq Compound - AI system with built-in tools */
    case GROQ_TEXT_COMPOUND = 'groq/compound';

    /** Groq Compound Mini - Smaller compound system */
    case GROQ_TEXT_COMPOUND_MINI = 'groq/compound-mini';

    // Groq Audio Models
    /** Whisper Large V3 - Highest accuracy speech-to-text on Groq */
    case GROQ_AUDIO_WHISPER_LARGE_V3 = 'whisper-large-v3';

    /** Whisper Large V3 Turbo - Balanced speed and accuracy */
    case GROQ_AUDIO_WHISPER_LARGE_V3_TURBO = 'whisper-large-v3-turbo';

    /** Canopy Labs Orpheus V1 English - TTS model */
    case GROQ_AUDIO_ORPHEUS_V1_ENGLISH = 'canopylabs/orpheus-v1-english';

    /** Canopy Labs Orpheus Arabic Saudi - Arabic TTS model */
    case GROQ_AUDIO_ORPHEUS_ARABIC_SAUDI = 'canopylabs/orpheus-arabic-saudi';

    // Groq Moderation Models
    /** Llama Guard 4 12B - Content moderation model */
    case GROQ_MODERATION_LLAMA_GUARD_4_12B = 'meta-llama/llama-guard-4-12b';

    /** OpenAI Safety GPT-OSS-20B - Safety moderation model */
    case GROQ_MODERATION_GPT_OSS_SAFEGUARD_20B = 'openai/gpt-oss-safeguard-20b';

    /** Llama Prompt Guard 2 86M - Prompt guard model */
    case GROQ_MODERATION_LLAMA_PROMPT_GUARD_2_86M = 'meta-llama/llama-prompt-guard-2-86m';

    /** Llama Prompt Guard 2 22M - Small prompt guard model */
    case GROQ_MODERATION_LLAMA_PROMPT_GUARD_2_22M = 'meta-llama/llama-prompt-guard-2-22m';

    // ==========================================
    // DEEPSEEK
    // ==========================================

    // DeepSeek Text Models
    /** DeepSeek Chat - Main DeepSeek conversational model (DeepSeek-V3.2 Non-thinking) */
    case DEEPSEEK_TEXT_DEEPSEEK_CHAT = 'deepseek-chat';

    /** DeepSeek Reasoner - DeepSeek reasoning model (DeepSeek-V3.2 Thinking Mode) */
    case DEEPSEEK_TEXT_DEEPSEEK_REASONER = 'deepseek-reasoner';

    // ==========================================
    // XAI
    // ==========================================

    // xAI Text Models
    /** Grok 4.1 Fast Reasoning - Best tool-calling model with 2M context, reasoning mode */
    case XAI_TEXT_GROK_4_1_FAST_REASONING = 'grok-4-1-fast-reasoning';

    /** Grok 4.1 Fast Non-Reasoning - Best tool-calling model without reasoning */
    case XAI_TEXT_GROK_4_1_FAST_NON_REASONING = 'grok-4-1-fast-non-reasoning';

    /** Grok 4 Fast Reasoning - Fast version of Grok 4 with reasoning */
    case XAI_TEXT_GROK_4_FAST_REASONING = 'grok-4-fast-reasoning';

    /** Grok 4 Fast Non-Reasoning - Fast version of Grok 4 without reasoning */
    case XAI_TEXT_GROK_4_FAST_NON_REASONING = 'grok-4-fast-non-reasoning';

    /** Grok 4 - Most intelligent model with native tool use and 256K context */
    case XAI_TEXT_GROK_4 = 'grok-4';

    /** Grok 3 - General availability model */
    case XAI_TEXT_GROK_3 = 'grok-3';

    /** Grok 3 Mini - Smaller version of Grok 3 */
    case XAI_TEXT_GROK_3_MINI = 'grok-3-mini';

    /** Grok Code Fast 1 - Fast coding model */
    case XAI_TEXT_GROK_CODE_FAST_1 = 'grok-code-fast-1';

    // xAI Vision Models
    /** Grok 2 Vision - Vision model */
    case XAI_TEXT_GROK_2_VISION = 'grok-2-vision-1212';

    // xAI Image Models
    /** Grok 2 Image - Image generation model */
    case XAI_IMAGE_GROK_2_IMAGE = 'grok-2-image-1212';

    // ==========================================
    // VOYAGEAI
    // ==========================================

    // VoyageAI Embedding Models
    /** Voyage 3 Large - Best general-purpose and multilingual retrieval quality */
    case VOYAGEAI_EMBEDDING_VOYAGE_3_LARGE = 'voyage-3-large';

    /** Voyage 3.5 - Optimized for general-purpose and multilingual retrieval */
    case VOYAGEAI_EMBEDDING_VOYAGE_3_5 = 'voyage-3.5';

    /** Voyage 3.5 Lite - Optimized for latency and cost */
    case VOYAGEAI_EMBEDDING_VOYAGE_3_5_LITE = 'voyage-3.5-lite';

    /** Voyage Code 3 - Optimized for code retrieval */
    case VOYAGEAI_EMBEDDING_VOYAGE_CODE_3 = 'voyage-code-3';

    /** Voyage Finance 2 - Optimized for finance retrieval and RAG */
    case VOYAGEAI_EMBEDDING_VOYAGE_FINANCE_2 = 'voyage-finance-2';

    /** Voyage Law 2 - Optimized for legal retrieval and RAG */
    case VOYAGEAI_EMBEDDING_VOYAGE_LAW_2 = 'voyage-law-2';

    /** Voyage 3 - General-purpose embedding model */
    case VOYAGEAI_EMBEDDING_VOYAGE_3 = 'voyage-3';

    /** Voyage 3 Lite - Efficient embedding model */
    case VOYAGEAI_EMBEDDING_VOYAGE_3_LITE = 'voyage-3-lite';

    // ==========================================
    // ELEVENLABS
    // ==========================================

    // ElevenLabs Audio Models
    /** Scribe V2 - State-of-the-art speech recognition in 90+ languages */
    case ELEVENLABS_AUDIO_SCRIBE_V2 = 'scribe_v2';

    /** Scribe V2 Realtime - Real-time speech recognition with low latency */
    case ELEVENLABS_AUDIO_SCRIBE_V2_REALTIME = 'scribe_v2_realtime';

    /** Scribe V1 - Speech-to-text with diarization support */
    case ELEVENLABS_AUDIO_SCRIBE_V1 = 'scribe_v1';

    // ==========================================
    // OPENROUTER
    // ==========================================

    // OpenRouter acts as a proxy for many models - add specific ones as needed

    /**
     * Get the provider prefix for filtering.
     */
    private static function providerPrefix(Provider $provider): string
    {
        return match ($provider) {
            Provider::OpenAI => 'OPENAI_',
            Provider::Anthropic => 'ANTHROPIC_',
            Provider::Gemini => 'GEMINI_',
            Provider::Mistral => 'MISTRAL_',
            Provider::Ollama => 'OLLAMA_',
            Provider::Groq => 'GROQ_',
            Provider::DeepSeek => 'DEEPSEEK_',
            Provider::XAI => 'XAI_',
            Provider::VoyageAI => 'VOYAGEAI_',
            Provider::ElevenLabs => 'ELEVENLABS_',
            Provider::OpenRouter => 'OPENROUTER_',
        };
    }

    /**
     * Get the Prism Provider enum for this model.
     */
    public function provider(): Provider
    {
        return match (true) {
            str_starts_with($this->name, 'OPENAI_') => Provider::OpenAI,
            str_starts_with($this->name, 'ANTHROPIC_') => Provider::Anthropic,
            str_starts_with($this->name, 'GEMINI_') => Provider::Gemini,
            str_starts_with($this->name, 'MISTRAL_') => Provider::Mistral,
            str_starts_with($this->name, 'OLLAMA_') => Provider::Ollama,
            str_starts_with($this->name, 'GROQ_') => Provider::Groq,
            str_starts_with($this->name, 'DEEPSEEK_') => Provider::DeepSeek,
            str_starts_with($this->name, 'XAI_') => Provider::XAI,
            str_starts_with($this->name, 'VOYAGEAI_') => Provider::VoyageAI,
            str_starts_with($this->name, 'ELEVENLABS_') => Provider::ElevenLabs,
            str_starts_with($this->name, 'OPENROUTER_') => Provider::OpenRouter,
            default => throw new \RuntimeException("Unknown provider prefix for model: {$this->name}"),
        };
    }

    /**
     * Get the model string value for use with Prism.
     */
    public function modelName(): string
    {
        return $this->value;
    }

    /**
     * Get all models for a specific provider.
     *
     * @return array<self>
     */
    public static function forProvider(Provider $provider): array
    {
        $prefix = self::providerPrefix($provider);

        return array_values(array_filter(
            self::cases(),
            fn (self $case): bool => str_starts_with($case->name, $prefix)
        ));
    }

    /**
     * Get all text models, optionally filtered by provider.
     *
     * @return array<self>
     */
    public static function textModels(?Provider $provider = null): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $case): bool => self::hasModelType($case, 'TEXT')
                && ($provider === null || str_starts_with($case->name, self::providerPrefix($provider)))
        ));
    }

    /**
     * Check if a model enum case has the specified type.
     */
    private static function hasModelType(self $case, string $type): bool
    {
        // Pattern is {PROVIDER}_{TYPE}_{MODEL_NAME}
        // Extract the second segment (after first underscore) and check if it matches
        $parts = explode('_', $case->name, 3);

        return isset($parts[1]) && $parts[1] === $type;
    }

    /**
     * Get all image models, optionally filtered by provider.
     *
     * @return array<self>
     */
    public static function imageModels(?Provider $provider = null): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $case): bool => self::hasModelType($case, 'IMAGE')
                && ($provider === null || str_starts_with($case->name, self::providerPrefix($provider)))
        ));
    }

    /**
     * Get all audio models, optionally filtered by provider.
     *
     * @return array<self>
     */
    public static function audioModels(?Provider $provider = null): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $case): bool => self::hasModelType($case, 'AUDIO')
                && ($provider === null || str_starts_with($case->name, self::providerPrefix($provider)))
        ));
    }

    /**
     * Get all video models, optionally filtered by provider.
     *
     * @return array<self>
     */
    public static function videoModels(?Provider $provider = null): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $case): bool => self::hasModelType($case, 'VIDEO')
                && ($provider === null || str_starts_with($case->name, self::providerPrefix($provider)))
        ));
    }

    /**
     * Get all embedding models, optionally filtered by provider.
     *
     * @return array<self>
     */
    public static function embeddingModels(?Provider $provider = null): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $case): bool => self::hasModelType($case, 'EMBEDDING')
                && ($provider === null || str_starts_with($case->name, self::providerPrefix($provider)))
        ));
    }

    /**
     * Get all moderation models, optionally filtered by provider.
     *
     * @return array<self>
     */
    public static function moderationModels(?Provider $provider = null): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $case): bool => self::hasModelType($case, 'MODERATION')
                && ($provider === null || str_starts_with($case->name, self::providerPrefix($provider)))
        ));
    }

    /**
     * Get text models formatted for Vuetify select/autocomplete components.
     * Returns an array of provider groups with models and enabled status.
     *
     * @return array<int, array{provider: string, providerLabel: string, enabled: bool, models: array<int, array{value: string, label: string}>}>
     */
    public static function toVuetifyOptions(): array
    {
        $groups = [];

        foreach (Provider::cases() as $provider) {
            $textModels = self::textModels($provider);

            if (empty($textModels)) {
                continue;
            }

            $groups[] = [
                'provider' => $provider->value,
                'providerLabel' => self::getProviderLabel($provider),
                'enabled' => self::isProviderEnabled($provider),
                'models' => array_map(fn (self $model) => [
                    'value' => $model->value,
                    'label' => $model->getLabel(),
                ], $textModels),
            ];
        }

        return $groups;
    }

    /**
     * Get a human-readable label for a provider.
     */
    public static function getProviderLabel(Provider $provider): string
    {
        return match ($provider) {
            Provider::OpenAI => 'OpenAI',
            Provider::Anthropic => 'Anthropic',
            Provider::Gemini => 'Google Gemini',
            Provider::Mistral => 'Mistral AI',
            Provider::Ollama => 'Ollama (Local)',
            Provider::Groq => 'Groq',
            Provider::DeepSeek => 'DeepSeek',
            Provider::XAI => 'xAI (Grok)',
            Provider::VoyageAI => 'Voyage AI',
            Provider::ElevenLabs => 'ElevenLabs',
            Provider::OpenRouter => 'OpenRouter',
        };
    }

    /**
     * Check if a provider is enabled (has API key configured).
     */
    public static function isProviderEnabled(Provider $provider): bool
    {
        $configKey = match ($provider) {
            Provider::OpenAI => 'prism.providers.openai.api_key',
            Provider::Anthropic => 'prism.providers.anthropic.api_key',
            Provider::Gemini => 'prism.providers.gemini.api_key',
            Provider::Mistral => 'prism.providers.mistral.api_key',
            Provider::Ollama => 'prism.providers.ollama.url',
            Provider::Groq => 'prism.providers.groq.api_key',
            Provider::DeepSeek => 'prism.providers.deepseek.api_key',
            Provider::XAI => 'prism.providers.xai.api_key',
            Provider::VoyageAI => 'prism.providers.voyageai.api_key',
            Provider::ElevenLabs => 'prism.providers.elevenlabs.api_key',
            Provider::OpenRouter => 'prism.providers.openrouter.api_key',
        };

        $value = config($configKey);

        return $value !== null && $value !== '';
    }

    /**
     * Get a human-readable label for this model.
     */
    public function getLabel(): string
    {
        $parts = explode('_', $this->name);

        // Remove provider and type prefixes (e.g., OPENAI_TEXT_)
        array_shift($parts);
        array_shift($parts);

        $label = implode(' ', array_map(
            fn (string $part) => ucfirst(strtolower($part)),
            $parts
        ));

        // Convert version numbers like "4 5" to "4.5"
        $label = preg_replace('/(\d)\s+(\d)/', '$1.$2', $label ?: $this->value);

        return $label ?: $this->value;
    }
}
