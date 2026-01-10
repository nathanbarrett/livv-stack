<script setup lang="ts">
import { computed } from 'vue'
import type { AiChatSession } from '@js/types/chat'

interface Props {
  sessions: AiChatSession[]
  currentSessionId?: number | null
}

const props = withDefaults(defineProps<Props>(), {
  currentSessionId: null,
})

const emit = defineEmits<{
  select: [session: AiChatSession]
  delete: [session: AiChatSession]
  new: []
}>()

function formatDate(dateString: string | null | undefined): string {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))

  if (days === 0) {
    return 'Today'
  } else if (days === 1) {
    return 'Yesterday'
  } else if (days < 7) {
    return `${days} days ago`
  } else {
    return date.toLocaleDateString()
  }
}

function getSessionTitle(session: AiChatSession): string {
  if (session.title) {
    return session.title
  }

  const firstMessage = session.messages?.[0]
  if (firstMessage && firstMessage.role === 'user') {
    const content = firstMessage.content
    return content.length > 50 ? content.substring(0, 50) + '...' : content
  }

  return 'New Chat'
}

const isSessionActive = computed(() => {
  return (sessionId: number) => sessionId === props.currentSessionId
})
</script>

<template>
  <div class="ai-chat-session-list">
    <div class="session-list-header pa-3">
      <v-btn
        block
        color="primary"
        prepend-icon="mdi-plus"
        @click="emit('new')"
      >
        New Chat
      </v-btn>
    </div>

    <v-divider />

    <div class="session-list-content">
      <v-list
        density="compact"
        class="pa-2"
      >
        <v-list-item
          v-for="session in sessions"
          :key="session.id"
          :active="isSessionActive(session.id)"
          class="session-item mb-1"
          rounded
          @click="emit('select', session)"
        >
          <template #prepend>
            <v-icon
              icon="mdi-chat-outline"
              size="small"
            />
          </template>

          <v-list-item-title class="text-body-2 session-title">
            {{ getSessionTitle(session) }}
          </v-list-item-title>

          <v-list-item-subtitle class="text-caption">
            {{ formatDate(session.updated_at || session.created_at) }}
          </v-list-item-subtitle>

          <template #append>
            <v-btn
              icon="mdi-delete-outline"
              size="x-small"
              variant="text"
              color="error"
              class="delete-btn"
              @click.stop="emit('delete', session)"
            />
          </template>
        </v-list-item>

        <div
          v-if="sessions.length === 0"
          class="empty-state text-center pa-4"
        >
          <v-icon
            icon="mdi-chat-outline"
            size="48"
            color="grey-lighten-1"
          />
          <div class="text-body-2 text-medium-emphasis mt-2">
            No chat sessions yet
          </div>
        </div>
      </v-list>
    </div>
  </div>
</template>

<style scoped>
.ai-chat-session-list {
  height: 100%;
  display: flex;
  flex-direction: column;
  background-color: rgb(var(--v-theme-surface));
  border-right: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.session-list-header {
  flex-shrink: 0;
}

.session-list-content {
  flex: 1;
  overflow-y: auto;
}

.session-item {
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.session-item:hover .delete-btn {
  opacity: 1;
}

.delete-btn {
  opacity: 0;
  transition: opacity 0.2s ease;
}

.session-item:hover .delete-btn,
.delete-btn:focus {
  opacity: 1;
}

.session-title {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 32px;
}
</style>
