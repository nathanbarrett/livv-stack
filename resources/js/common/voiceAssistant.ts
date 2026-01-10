import { reactive } from 'vue';

export type VoiceAssistantMode = 'general' | 'kanban';

export const voiceAssistantStore = reactive({
    isOpen: false,
    mode: 'general' as VoiceAssistantMode,

    open(mode: VoiceAssistantMode = 'general') {
        this.mode = mode;
        this.isOpen = true;
    },

    close() {
        this.isOpen = false;
    },

    setMode(mode: VoiceAssistantMode) {
        this.mode = mode;
    },
});

export function openVoiceAssistant(mode: VoiceAssistantMode = 'general'): void {
    voiceAssistantStore.open(mode);
}

export function closeVoiceAssistant(): void {
    voiceAssistantStore.close();
}
