<script setup lang="ts">
import { computed, watch } from 'vue'
import { useDisplay } from 'vuetify'
import { voiceAssistantStore } from '@js/common/voiceAssistant'
import { useRealtimeChat } from '@js/composables/useRealtimeChat'
import VoiceAssistantModeSelector from '@js/components/VoiceAssistantModeSelector.vue'
import VoiceAssistantTranscript from '@js/components/VoiceAssistantTranscript.vue'
import VoiceAssistantVisualizer from '@js/components/VoiceAssistantVisualizer.vue'
import type { VoiceAssistantMode } from '@js/common/voiceAssistant'

const { mobile } = useDisplay()
const {
    isConnected,
    isConnecting,
    isRecording,
    isAiSpeaking,
    transcripts,
    connect,
    disconnect,
} = useRealtimeChat()

const isOpen = computed({
    get: () => voiceAssistantStore.isOpen,
    set: (val: boolean) => (val ? voiceAssistantStore.open() : voiceAssistantStore.close()),
})

// Auto-connect when dialog opens
watch(isOpen, async (open) => {
    if (open && !isConnected.value && !isConnecting.value) {
        await connect(voiceAssistantStore.mode)
    } else if (!open && isConnected.value) {
        await disconnect()
    }
})

async function handleModeChange(mode: VoiceAssistantMode): Promise<void> {
    voiceAssistantStore.setMode(mode)
    if (isConnected.value) {
        await disconnect()
        await connect(mode)
    }
}

async function handleClose(): Promise<void> {
    if (isConnected.value) {
        await disconnect()
    }
    voiceAssistantStore.close()
}

async function handleConnect(): Promise<void> {
    await connect(voiceAssistantStore.mode)
}

async function handleDisconnect(): Promise<void> {
    await disconnect()
}
</script>

<template>
    <v-dialog
        v-model="isOpen"
        :fullscreen="mobile"
        :width="mobile ? undefined : 800"
        :max-width="mobile ? undefined : '90vw'"
        persistent
        scrollable
    >
        <v-card class="d-flex flex-column" :height="mobile ? '100%' : '80vh'">
            <v-card-title class="d-flex align-center ga-2">
                <v-icon icon="mdi-microphone" />
                <span>Voice Assistant</span>
                <v-spacer />
                <VoiceAssistantModeSelector
                    :mode="voiceAssistantStore.mode"
                    :disabled="isConnecting"
                    @update:mode="handleModeChange"
                />
                <v-btn
                    icon="mdi-close"
                    variant="text"
                    size="small"
                    @click="handleClose"
                />
            </v-card-title>

            <v-divider />

            <v-card-text class="flex-grow-1 overflow-y-auto pa-4">
                <VoiceAssistantTranscript :transcripts="transcripts" />
            </v-card-text>

            <v-divider />

            <v-card-actions class="pa-4 justify-center flex-column ga-4">
                <VoiceAssistantVisualizer
                    :is-connected="isConnected"
                    :is-recording="isRecording"
                    :is-ai-speaking="isAiSpeaking"
                />

                <div class="d-flex ga-2">
                    <v-btn
                        v-if="!isConnected"
                        color="success"
                        size="large"
                        :loading="isConnecting"
                        @click="handleConnect"
                    >
                        <v-icon icon="mdi-phone" class="mr-2" />
                        Connect
                    </v-btn>
                    <v-btn
                        v-else
                        color="error"
                        size="large"
                        @click="handleDisconnect"
                    >
                        <v-icon icon="mdi-phone-hangup" class="mr-2" />
                        Disconnect
                    </v-btn>
                </div>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
