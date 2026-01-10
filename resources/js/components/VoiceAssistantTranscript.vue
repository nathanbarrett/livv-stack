<script setup lang="ts">
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue'
import type { RealtimeTranscript } from '@js/types/realtime'

const props = defineProps<{
    transcripts: readonly RealtimeTranscript[]
}>()

const containerRef = ref<HTMLElement | null>(null)
const isScrollLocked = ref(false)

// Threshold in pixels to consider "at bottom"
const SCROLL_THRESHOLD = 50

function isAtBottom(): boolean {
    if (!containerRef.value) return true
    const { scrollTop, scrollHeight, clientHeight } = containerRef.value
    return scrollHeight - scrollTop - clientHeight <= SCROLL_THRESHOLD
}

function handleScroll(): void {
    // If user scrolls to bottom, unlock auto-scroll
    // If user scrolls up, lock auto-scroll
    isScrollLocked.value = !isAtBottom()
}

function scrollToBottom(): void {
    if (containerRef.value) {
        containerRef.value.scrollTop = containerRef.value.scrollHeight
    }
}

// Auto-scroll to bottom when new transcripts arrive (if not scroll locked)
watch(
    () => props.transcripts.length,
    async () => {
        await nextTick()
        if (!isScrollLocked.value) {
            scrollToBottom()
        }
    }
)

// Also watch for content changes in existing transcripts (partial updates)
watch(
    () => props.transcripts.map((t) => t.text).join(''),
    async () => {
        await nextTick()
        if (!isScrollLocked.value) {
            scrollToBottom()
        }
    }
)

onMounted(() => {
    containerRef.value?.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
    containerRef.value?.removeEventListener('scroll', handleScroll)
})

function formatTime(date: Date): string {
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
    <div ref="containerRef" class="transcript-container">
        <div v-if="transcripts.length === 0" class="text-center text-medium-emphasis py-8">
            <v-icon icon="mdi-microphone" size="48" class="mb-2" />
            <p>Start speaking to begin a conversation</p>
        </div>

        <div
            v-for="transcript in transcripts"
            :key="transcript.id"
            class="transcript-message mb-3"
            :class="{
                'transcript-user': transcript.role === 'user',
                'transcript-assistant': transcript.role === 'assistant',
                'transcript-partial': transcript.isPartial,
            }"
        >
            <div class="d-flex align-center mb-1">
                <v-icon
                    :icon="transcript.role === 'user' ? 'mdi-account' : 'mdi-robot'"
                    size="small"
                    class="mr-1"
                />
                <span class="text-caption text-medium-emphasis">
                    {{ transcript.role === 'user' ? 'You' : 'Assistant' }}
                </span>
                <v-spacer />
                <span class="text-caption text-disabled">
                    {{ formatTime(transcript.timestamp) }}
                </span>
            </div>
            <div class="transcript-text">
                {{ transcript.text }}
                <span v-if="transcript.isPartial" class="typing-indicator">
                    <span class="dot" />
                    <span class="dot" />
                    <span class="dot" />
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.transcript-container {
    min-height: 200px;
}

.transcript-message {
    padding: 12px 16px;
    border-radius: 12px;
    max-width: 85%;
}

.transcript-user {
    background-color: rgb(var(--v-theme-primary), 0.1);
    margin-left: auto;
}

.transcript-assistant {
    background-color: rgb(var(--v-theme-surface-light));
    color: rgb(var(--v-theme-on-surface));
    margin-right: auto;
    border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.transcript-partial {
    opacity: 0.8;
}

.transcript-text {
    white-space: pre-wrap;
    word-break: break-word;
}

.typing-indicator {
    display: inline-flex;
    align-items: center;
    margin-left: 4px;
}

.typing-indicator .dot {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background-color: currentColor;
    margin: 0 1px;
    animation: typing 1.4s infinite ease-in-out;
}

.typing-indicator .dot:nth-child(1) {
    animation-delay: 0s;
}

.typing-indicator .dot:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-indicator .dot:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%,
    60%,
    100% {
        opacity: 0.3;
    }
    30% {
        opacity: 1;
    }
}
</style>
