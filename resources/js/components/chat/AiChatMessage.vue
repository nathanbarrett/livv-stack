<script setup lang="ts">
import { computed } from 'vue'
import type { AiChatMessage } from '@js/types/chat'
import { useMarkdown } from '@js/composables/useMarkdown'
import AiChatAttachment from '@js/components/chat/AiChatAttachment.vue'

interface Props {
  message: AiChatMessage
}

const props = defineProps<Props>()

const { renderMarkdown } = useMarkdown()

const isUser = computed(() => props.message.role === 'user')
const alignment = computed(() => isUser.value ? 'end' : 'start')
const bgColor = computed(() => isUser.value ? 'primary' : 'surface-variant')

const renderedContent = computed(() => {
  if (isUser.value) {
    return props.message.content
  }
  return renderMarkdown(props.message.content)
})

function formatTimestamp(timestamp: string | null | undefined): string {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <div
    class="ai-chat-message d-flex mb-3"
    :class="`justify-${alignment}`"
  >
    <div class="message-wrapper">
      <v-card
        :color="bgColor"
        class="message-card"
        :class="{ 'user-message': isUser, 'assistant-message': !isUser }"
        variant="flat"
      >
        <v-card-text class="pa-3">
          <div
            v-if="isUser"
            class="message-content"
          >
            {{ message.content }}
          </div>
          <!-- eslint-disable-next-line vue/no-v-html -->
          <div
            v-else
            class="message-content markdown-content"
            v-html="renderedContent"
          />

          <div
            v-if="message.attachments && message.attachments.length > 0"
            class="attachments-grid mt-2"
          >
            <AiChatAttachment
              v-for="attachment in message.attachments"
              :key="attachment.id"
              :attachment="attachment"
              :removable="false"
            />
          </div>
        </v-card-text>
      </v-card>

      <div class="message-meta text-caption text-medium-emphasis mt-1">
        <span v-if="message.created_at">{{ formatTimestamp(message.created_at) }}</span>
        <span
          v-if="!isUser && message.model"
          class="ml-2"
        >
          {{ message.model }}
        </span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.ai-chat-message {
  width: 100%;
}

.message-wrapper {
  max-width: 75%;
  min-width: 100px;
}

.message-card {
  border-radius: 12px;
  word-wrap: break-word;
  word-break: break-word;
}

.user-message {
  color: rgb(var(--v-theme-on-primary));
}

.assistant-message {
  color: rgb(var(--v-theme-on-surface-variant));
}

.message-content {
  line-height: 1.5;
  white-space: pre-wrap;
}

.markdown-content :deep(pre) {
  margin: 8px 0;
  border-radius: 4px;
  overflow-x: auto;
}

.markdown-content :deep(code) {
  font-family: 'Courier New', monospace;
  font-size: 0.9em;
}

.markdown-content :deep(p) {
  margin: 8px 0;
}

.markdown-content :deep(ul),
.markdown-content :deep(ol) {
  margin: 8px 0;
  padding-left: 24px;
}

.markdown-content :deep(blockquote) {
  border-left: 3px solid rgba(var(--v-theme-primary), 0.3);
  padding-left: 12px;
  margin: 8px 0;
  font-style: italic;
}

.attachments-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.message-meta {
  padding: 0 8px;
}
</style>
