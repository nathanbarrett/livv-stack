<script setup lang="ts">
import { ref, computed } from 'vue'
import type { AiChatAttachment } from '@js/types/chat'
import AiChatAttachmentCard from '@js/components/chat/AiChatAttachment.vue'

interface Props {
  disabled?: boolean
  pendingAttachments?: AiChatAttachment[]
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  pendingAttachments: () => [],
})

const emit = defineEmits<{
  send: [content: string]
  upload: [file: File]
  removeAttachment: [attachmentId: number]
}>()

const message = ref('')
const isDragging = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)

const canSend = computed(() => {
  return message.value.trim().length > 0 && !props.disabled
})

function handleSend(): void {
  if (!canSend.value) return

  emit('send', message.value.trim())
  message.value = ''
}

function handleKeydown(event: KeyboardEvent): void {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault()
    handleSend()
  }
}

function handleDragEnter(event: DragEvent): void {
  event.preventDefault()
  isDragging.value = true
}

function handleDragLeave(event: DragEvent): void {
  event.preventDefault()
  const target = event.target as HTMLElement
  const relatedTarget = event.relatedTarget as HTMLElement

  if (!relatedTarget || !target.contains(relatedTarget)) {
    isDragging.value = false
  }
}

function handleDragOver(event: DragEvent): void {
  event.preventDefault()
}

function handleDrop(event: DragEvent): void {
  event.preventDefault()
  isDragging.value = false

  const files = event.dataTransfer?.files
  if (files && files.length > 0) {
    for (let i = 0; i < files.length; i++) {
      emit('upload', files[i])
    }
  }
}

function handleFileSelect(event: Event): void {
  const target = event.target as HTMLInputElement
  const files = target.files
  if (files && files.length > 0) {
    for (let i = 0; i < files.length; i++) {
      emit('upload', files[i])
    }
  }
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

function openFilePicker(): void {
  fileInput.value?.click()
}
</script>

<template>
  <div class="ai-chat-input">
    <div
      v-if="pendingAttachments.length > 0"
      class="pending-attachments pa-2"
    >
      <div class="attachments-grid">
        <AiChatAttachmentCard
          v-for="attachment in pendingAttachments"
          :key="attachment.id"
          :attachment="attachment"
          :removable="true"
          @remove="emit('removeAttachment', attachment.id)"
        />
      </div>
    </div>

    <div
      class="input-container pa-3"
      :class="{ 'dragging': isDragging }"
      @dragenter="handleDragEnter"
      @dragleave="handleDragLeave"
      @dragover="handleDragOver"
      @drop="handleDrop"
    >
      <div
        v-if="isDragging"
        class="drag-overlay"
      >
        <v-icon
          icon="mdi-upload"
          size="48"
          color="primary"
        />
        <div class="text-h6 mt-2">
          Drop files here
        </div>
      </div>

      <div class="d-flex align-end ga-2">
        <input
          ref="fileInput"
          type="file"
          multiple
          hidden
          @change="handleFileSelect"
        >

        <v-btn
          icon="mdi-paperclip"
          variant="text"
          size="small"
          :disabled="disabled"
          @click="openFilePicker"
        />

        <v-textarea
          v-model="message"
          placeholder="Type your message..."
          rows="1"
          auto-grow
          max-rows="5"
          variant="outlined"
          density="comfortable"
          hide-details
          :disabled="disabled"
          @keydown="handleKeydown"
        />

        <v-btn
          icon="mdi-send"
          color="primary"
          size="small"
          :disabled="!canSend"
          @click="handleSend"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.ai-chat-input {
  border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  background-color: rgb(var(--v-theme-surface));
}

.pending-attachments {
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.attachments-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.input-container {
  position: relative;
}

.input-container.dragging {
  background-color: rgba(var(--v-theme-primary), 0.05);
  border: 2px dashed rgb(var(--v-theme-primary));
  border-radius: 8px;
}

.drag-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: rgba(var(--v-theme-surface), 0.95);
  z-index: 10;
  border-radius: 8px;
}
</style>
