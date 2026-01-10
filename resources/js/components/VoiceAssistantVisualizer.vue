<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    isConnected: boolean
    isRecording: boolean
    isAiSpeaking: boolean
}>()

const statusText = computed(() => {
    if (!props.isConnected) {
        return 'Not connected'
    }
    if (props.isRecording) {
        return 'Listening...'
    }
    if (props.isAiSpeaking) {
        return 'Speaking...'
    }
    return 'Ready'
})

const statusColor = computed(() => {
    if (!props.isConnected) {
        return 'grey'
    }
    if (props.isRecording) {
        return 'error'
    }
    if (props.isAiSpeaking) {
        return 'primary'
    }
    return 'success'
})

const isAnimating = computed(() => props.isRecording || props.isAiSpeaking)
</script>

<template>
    <div class="visualizer-container d-flex flex-column align-center">
        <div
            class="visualizer-orb"
            :class="{
                'visualizer-orb--active': isAnimating,
                'visualizer-orb--recording': isRecording,
                'visualizer-orb--speaking': isAiSpeaking,
            }"
        >
            <v-icon
                :icon="isRecording ? 'mdi-microphone' : isAiSpeaking ? 'mdi-volume-high' : 'mdi-microphone-outline'"
                :color="statusColor"
                size="32"
            />
        </div>

        <div class="d-flex align-center mt-2">
            <v-icon
                icon="mdi-circle"
                :color="statusColor"
                size="8"
                class="mr-1"
            />
            <span class="text-body-2" :class="`text-${statusColor}`">
                {{ statusText }}
            </span>
        </div>

        <div v-if="isAnimating" class="visualizer-bars mt-3">
            <div
                v-for="i in 5"
                :key="i"
                class="visualizer-bar"
                :class="`bg-${statusColor}`"
                :style="{ animationDelay: `${i * 0.1}s` }"
            />
        </div>
    </div>
</template>

<style scoped>
.visualizer-container {
    min-height: 120px;
}

.visualizer-orb {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgb(var(--v-theme-surface-variant));
    transition: all 0.3s ease;
}

.visualizer-orb--active {
    animation: pulse 1.5s ease-in-out infinite;
}

.visualizer-orb--recording {
    background-color: rgba(var(--v-theme-error), 0.15);
}

.visualizer-orb--speaking {
    background-color: rgba(var(--v-theme-primary), 0.15);
}

@keyframes pulse {
    0%,
    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(var(--v-theme-primary), 0.4);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 20px 10px rgba(var(--v-theme-primary), 0);
    }
}

.visualizer-bars {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    height: 32px;
}

.visualizer-bar {
    width: 4px;
    height: 16px;
    border-radius: 2px;
    animation: wave 0.8s ease-in-out infinite;
}

@keyframes wave {
    0%,
    100% {
        height: 8px;
    }
    50% {
        height: 28px;
    }
}
</style>
