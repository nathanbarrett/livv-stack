<script setup lang="ts">
import type { VoiceAssistantMode } from '@js/common/voiceAssistant'

interface ModeOption {
    value: VoiceAssistantMode
    label: string
    icon: string
}

defineProps<{
    mode: VoiceAssistantMode
    disabled?: boolean
}>()

const emit = defineEmits<{
    'update:mode': [mode: VoiceAssistantMode]
}>()

const modes: ModeOption[] = [
    { value: 'general', label: 'Chat about anything', icon: 'mdi-chat' },
    { value: 'kanban', label: 'Chat about kanban', icon: 'mdi-view-column' },
]
</script>

<template>
    <v-select
        :model-value="mode"
        :items="modes"
        item-title="label"
        item-value="value"
        density="compact"
        variant="outlined"
        hide-details
        :disabled="disabled"
        style="min-width: 220px"
        @update:model-value="emit('update:mode', $event)"
    >
        <template #selection="{ item }">
            <v-icon :icon="item.raw.icon" size="small" class="mr-1" />
            {{ item.raw.label }}
        </template>
        <template #item="{ item, props: itemProps }">
            <v-list-item v-bind="itemProps">
                <template #prepend>
                    <v-icon :icon="item.raw.icon" size="small" />
                </template>
            </v-list-item>
        </template>
    </v-select>
</template>
