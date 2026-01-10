<script setup lang="ts">
import { ref, watch, nextTick } from 'vue'
import type { AiChatMessage } from '@js/types/chat'
import AiChatMessageCard from '@js/components/chat/AiChatMessage.vue'

interface Props {
  messages: AiChatMessage[]
  isLoading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
})

const messagesContainer = ref<HTMLElement | null>(null)

function scrollToBottom(): void {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

watch(() => props.messages.length, () => {
  scrollToBottom()
})

watch(() => props.isLoading, (newVal) => {
  if (newVal) {
    scrollToBottom()
  }
})
</script>

<template>
  <div
    ref="messagesContainer"
    class="ai-chat-messages"
  >
    <div
      v-if="messages.length === 0 && !isLoading"
      class="empty-state"
    >
      <v-icon
        icon="mdi-chat-outline"
        size="64"
        color="grey-lighten-1"
      />
      <div class="text-h6 text-medium-emphasis mt-4">
        No messages yet
      </div>
      <div class="text-body-2 text-medium-emphasis mt-2">
        Start a conversation by typing a message below
      </div>
    </div>

    <div
      v-else
      class="messages-list pa-4"
    >
      <AiChatMessageCard
        v-for="message in messages"
        :key="message.id"
        :message="message"
      />

      <div
        v-if="isLoading"
        class="loading-indicator"
      >
        <v-card
          class="pa-4"
          color="surface-variant"
          variant="tonal"
          max-width="200"
        >
          <div class="d-flex align-center ga-3">
            <div class="loader ml-10"></div>
          </div>
        </v-card>
      </div>
    </div>
  </div>
</template>

<style scoped>
.ai-chat-messages {
  flex: 1;
  overflow-y: auto;
  height: 100%;
  background-color: rgb(var(--v-theme-surface));
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  padding: 32px;
}

.messages-list {
  min-height: 100%;
  display: flex;
  flex-direction: column;
}

.loading-indicator {
  margin-top: 8px;
}

/* HTML: <div class="loader"></div> */
.loader {
    width: 15px;
    aspect-ratio: 1;
    border-radius: 50%;
    animation: l5 1s infinite linear alternate;
}
@keyframes l5 {
    0%  {box-shadow: 20px 0 #000, -20px 0 #0002;background: #000 }
    33% {box-shadow: 20px 0 #000, -20px 0 #0002;background: #0002}
    66% {box-shadow: 20px 0 #0002,-20px 0 #000; background: #0002}
    100%{box-shadow: 20px 0 #0002,-20px 0 #000; background: #000 }
}
</style>
