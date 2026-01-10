<script setup lang="ts">
import { computed } from 'vue'
import type { AiChatAttachment } from '@js/types/chat'

interface Props {
  attachment: AiChatAttachment
  removable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  removable: false,
})

const emit = defineEmits<{
  remove: []
}>()

const isImage = computed(() => {
  return props.attachment.mime_type.startsWith('image/')
})

const fileIcon = computed(() => {
  const mimeType = props.attachment.mime_type.toLowerCase()

  if (mimeType.startsWith('image/')) return 'mdi-file-image'
  if (mimeType.startsWith('video/')) return 'mdi-file-video'
  if (mimeType.startsWith('audio/')) return 'mdi-file-music'
  if (mimeType.includes('pdf')) return 'mdi-file-pdf-box'
  if (mimeType.includes('word') || mimeType.includes('document')) return 'mdi-file-word'
  if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'mdi-file-excel'
  if (mimeType.includes('powerpoint') || mimeType.includes('presentation')) return 'mdi-file-powerpoint'
  if (mimeType.includes('zip') || mimeType.includes('archive')) return 'mdi-folder-zip'
  if (mimeType.includes('text/')) return 'mdi-file-document'

  return 'mdi-file'
})

function formatFileSize(bytes: number): string {
  if (bytes === 0) return '0 Bytes'

  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}
</script>

<template>
  <v-card
    class="ai-chat-attachment"
    variant="outlined"
  >
    <div class="attachment-content">
      <div
        v-if="isImage && attachment.url"
        class="image-preview"
      >
        <v-img
          :src="attachment.url"
          :alt="attachment.original_filename"
          cover
          height="80"
        />
      </div>

      <div
        v-else
        class="file-icon-container"
      >
        <v-icon
          :icon="fileIcon"
          size="48"
          color="primary"
        />
      </div>

      <div class="attachment-info pa-2">
        <div class="text-caption font-weight-medium attachment-filename">
          {{ attachment.original_filename }}
        </div>
        <div class="text-caption text-medium-emphasis">
          {{ formatFileSize(attachment.size) }}
        </div>
      </div>

      <v-btn
        v-if="removable"
        icon="mdi-close"
        size="x-small"
        variant="text"
        color="error"
        class="remove-btn"
        @click="emit('remove')"
      />
    </div>
  </v-card>
</template>

<style scoped>
.ai-chat-attachment {
  width: 120px;
  position: relative;
  overflow: hidden;
}

.attachment-content {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.image-preview {
  width: 100%;
  flex-shrink: 0;
}

.file-icon-container {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 80px;
  background-color: rgba(var(--v-theme-primary), 0.05);
}

.attachment-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-height: 0;
}

.attachment-filename {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  line-height: 1.2;
}

.remove-btn {
  position: absolute;
  top: 4px;
  right: 4px;
  background-color: rgba(var(--v-theme-surface), 0.9);
}
</style>
