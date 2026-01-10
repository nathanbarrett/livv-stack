<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { useDisplay } from 'vuetify'
import { useAiChat } from '@js/composables/useAiChat'
import { useNotificationSound } from '@js/composables/useNotificationSound'
import { confirmDialog } from '@js/common/confirm'
import AiChatSessionList from '@js/components/chat/AiChatSessionList.vue'
import AiChatMessages from '@js/components/chat/AiChatMessages.vue'
import AiChatInput from '@js/components/chat/AiChatInput.vue'
import AiChatModelSelector from '@js/components/chat/AiChatModelSelector.vue'
import AiChatMemoryDialog from '@js/components/chat/AiChatMemoryDialog.vue'
import type { AiChatSession, ProviderModelGroup } from '@js/types/chat'

const { smAndDown } = useDisplay()
const isSidebarOpen = ref(true)

const {
  currentSession,
  models,
  isLoading,
  messages,
  pendingAttachments,
  sortedSessions,
  loadSessions,
  createSession,
  updateSession,
  deleteSession,
  selectSession,
  sendMessage,
  uploadAttachment,
  removePendingAttachment,
} = useAiChat()

const { isMuted, toggleMute, playSound } = useNotificationSound()

// Provider priority for default model selection: xAI (Grok), OpenAI, Anthropic
const PROVIDER_PRIORITY = ['xai', 'openai', 'anthropic']

// Get default model based on provider priority
function getDefaultModel(modelGroups: ProviderModelGroup[]): string {
  for (const providerName of PROVIDER_PRIORITY) {
    const group = modelGroups.find(
      (g) => g.provider === providerName && g.enabled && g.models.length > 0
    )
    if (group) {
      return group.models[0].value
    }
  }
  // Fallback to first enabled provider's first model
  const firstEnabled = modelGroups.find((g) => g.enabled && g.models.length > 0)
  return firstEnabled?.models[0]?.value || ''
}

// Track selected model independently (for when no session exists yet)
const selectedModel = ref<string>('')

// Track previous loading state to detect when loading completes
const wasLoading = ref(false)

// Initialize selected model when models are available
watch(
  models,
  (newModels) => {
    if (newModels.length > 0 && !selectedModel.value) {
      selectedModel.value = getDefaultModel(newModels)
    }
  },
  { immediate: true }
)

// Sync selected model with current session's model
watch(
  () => currentSession.value?.model,
  (sessionModel) => {
    if (sessionModel) {
      selectedModel.value = sessionModel
    }
  }
)

// Load initial data
onMounted(async () => {
  await loadSessions()
})

// Play sound when loading completes (AI response received)
watch(
  () => isLoading.value,
  (newVal) => {
    if (wasLoading.value && !newVal) {
      playSound()
    }
    wasLoading.value = newVal
  }
)

async function handleNewChat(): Promise<void> {
  const modelToUse = selectedModel.value || getDefaultModel(models.value)
  if (!modelToUse) return

  await createSession({
    model: modelToUse,
  })
}

async function handleSelectSession(session: AiChatSession): Promise<void> {
  selectSession(session)
  // Close sidebar on mobile after selecting a session
  if (smAndDown.value) {
    isSidebarOpen.value = false
  }
}

async function handleDeleteSession(session: AiChatSession): Promise<void> {
  const confirmed = await confirmDialog({
    title: 'Delete Chat Session',
    message: 'Are you sure you want to delete this chat session? This action cannot be undone.',
    confirmButtonText: 'Delete',
    confirmButtonColor: 'error',
  })

  if (confirmed) {
    await deleteSession(session.id)
  }
}

async function handleSend(content: string): Promise<void> {
  if (!currentSession.value) {
    await handleNewChat()
  }

  await sendMessage(content)
}

async function handleUpload(file: File): Promise<void> {
  if (!currentSession.value) {
    await handleNewChat()
  }

  await uploadAttachment(file)
}

function handleModelChange(newModel: string): void {
  selectedModel.value = newModel
  if (currentSession.value) {
    updateSession(currentSession.value.id, { model: newModel })
  }
}

// Get the label of the currently selected model
const selectedModelLabel = computed(() => {
  for (const group of models.value) {
    const model = group.models.find((m) => m.value === selectedModel.value)
    if (model) {
      return model.label
    }
  }
  return ''
})

// Get the provider label of the currently selected model
const selectedProviderLabel = computed(() => {
  for (const group of models.value) {
    const model = group.models.find((m) => m.value === selectedModel.value)
    if (model) {
      return group.providerLabel
    }
  }
  return ''
})

// Memory dialog state
const isMemoryDialogOpen = ref(false)
</script>

<template>
  <div
    v-fill-remaining-height
    class="ai-chat-box"
  >
    <v-navigation-drawer
      v-model="isSidebarOpen"
      :permanent="!smAndDown"
      :temporary="smAndDown"
      width="280"
      class="chat-sidebar"
    >
      <AiChatSessionList
        :sessions="sortedSessions"
        :current-session-id="currentSession?.id"
        @new="handleNewChat"
        @select="handleSelectSession"
        @delete="handleDeleteSession"
      />
    </v-navigation-drawer>

    <div :class="['chat-main', { 'chat-main--mobile': smAndDown }]">
      <div class="chat-header">
        <div class="chat-header-content">
          <v-btn
            v-if="smAndDown"
            icon="mdi-menu"
            variant="text"
            size="small"
            @click="isSidebarOpen = !isSidebarOpen"
          />

          <AiChatModelSelector
            v-if="models.length > 0"
            :model-value="selectedModel"
            :models="models"
            :class="['model-selector', { 'model-selector--mobile': smAndDown }]"
            @update:model-value="handleModelChange"
          />

          <div
            v-if="selectedModelLabel && !smAndDown"
            class="selected-model-info"
          >
            <v-chip
              size="small"
              variant="tonal"
              color="primary"
            >
              {{ selectedProviderLabel }}: {{ selectedModelLabel }}
            </v-chip>
          </div>

          <v-btn
            icon="mdi-brain"
            variant="text"
            size="small"
            title="AI Memories"
            @click="isMemoryDialogOpen = true"
          />
        </div>

        <v-btn
          :icon="isMuted ? 'mdi-volume-off' : 'mdi-volume-high'"
          variant="text"
          size="small"
          @click="toggleMute"
        />

        <AiChatMemoryDialog v-model="isMemoryDialogOpen" />
      </div>

      <div class="chat-content">
        <AiChatMessages
          :messages="messages"
          :is-loading="isLoading"
        />
      </div>

      <AiChatInput
        :disabled="isLoading"
        :pending-attachments="pendingAttachments"
        @send="handleSend"
        @upload="handleUpload"
        @remove-attachment="removePendingAttachment"
      />
    </div>
  </div>
</template>

<style scoped>
.ai-chat-box {
  display: flex;
  width: 100%;
  background-color: rgb(var(--v-theme-background));
}

.chat-sidebar {
  flex-shrink: 0;
}

.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  margin-left: 280px;
}

.chat-main--mobile {
  margin-left: 0;
}

.chat-header {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  background-color: rgb(var(--v-theme-surface));
}

.chat-header-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

.model-selector {
  width: 300px;
}

.model-selector--mobile {
  width: 180px;
  flex-shrink: 1;
}

.selected-model-info {
  display: flex;
  align-items: center;
}

.chat-content {
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
}
</style>
