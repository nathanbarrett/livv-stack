<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { success, error } from '@js/common/snackbar'
import { confirmDialog } from '@js/common/confirm'
import type { UserMemory, MemoriesResponse, MemoryResponse } from '@js/types/chat'

interface Props {
  modelValue: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const isOpen = computed({
  get: () => props.modelValue,
  set: (value: boolean) => emit('update:modelValue', value),
})

const memories = ref<UserMemory[]>([])
const isLoading = ref(false)
const editingMemory = ref<UserMemory | null>(null)
const editValue = ref('')

const groupedMemories = computed(() => {
  const groups: Record<string, UserMemory[]> = {}

  for (const memory of memories.value) {
    if (!groups[memory.type]) {
      groups[memory.type] = []
    }
    groups[memory.type].push(memory)
  }

  return Object.entries(groups).sort(([a], [b]) => a.localeCompare(b))
})

async function loadMemories(): Promise<void> {
  isLoading.value = true

  try {
    const response = await fetch('/api/chat/memories')
    const data: MemoriesResponse = await response.json()
    memories.value = data.memories
  } catch {
    error('Failed to load memories')
  } finally {
    isLoading.value = false
  }
}

function startEditing(memory: UserMemory): void {
  editingMemory.value = memory
  editValue.value = memory.value
}

function cancelEditing(): void {
  editingMemory.value = null
  editValue.value = ''
}

async function saveEdit(): Promise<void> {
  if (!editingMemory.value) return

  try {
    const response = await fetch(`/api/chat/memories/${editingMemory.value.id}`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify({ value: editValue.value }),
    })

    if (!response.ok) {
      throw new Error('Failed to update')
    }

    const data: MemoryResponse = await response.json()
    const index = memories.value.findIndex((m) => m.id === data.memory.id)

    if (index !== -1) {
      memories.value[index] = data.memory
    }

    success('Memory updated')
    cancelEditing()
  } catch {
    error('Failed to update memory')
  }
}

async function deleteMemory(memory: UserMemory): Promise<void> {
  const confirmed = await confirmDialog({
    title: 'Delete Memory',
    message: `Are you sure you want to delete "${memory.key}"? This cannot be undone.`,
    confirmButtonText: 'Delete',
    confirmButtonColor: 'error',
  })

  if (!confirmed) return

  try {
    const response = await fetch(`/api/chat/memories/${memory.id}`, {
      method: 'DELETE',
      headers: {
        'X-XSRF-TOKEN': getCsrfToken(),
      },
    })

    if (!response.ok) {
      throw new Error('Failed to delete')
    }

    memories.value = memories.value.filter((m) => m.id !== memory.id)
    success('Memory deleted')
  } catch {
    error('Failed to delete memory')
  }
}

function getCsrfToken(): string {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return match ? decodeURIComponent(match[1]) : ''
}

watch(isOpen, (newVal) => {
  if (newVal) {
    loadMemories()
  }
})
</script>

<template>
  <v-dialog
    v-model="isOpen"
    max-width="700"
    scrollable
  >
    <v-card>
      <v-card-title class="d-flex align-center">
        <v-icon
          icon="mdi-brain"
          class="mr-2"
        />
        AI Memories
        <v-spacer />
        <v-btn
          icon="mdi-close"
          variant="text"
          size="small"
          @click="isOpen = false"
        />
      </v-card-title>

      <v-card-subtitle class="pb-2">
        Information the AI has remembered about you across all chat sessions.
      </v-card-subtitle>

      <v-divider />

      <v-card-text class="pa-0">
        <v-progress-linear
          v-if="isLoading"
          indeterminate
          color="primary"
        />

        <div
          v-else-if="memories.length === 0"
          class="pa-6 text-center text-medium-emphasis"
        >
          <v-icon
            icon="mdi-brain"
            size="48"
            class="mb-2"
          />
          <p>No memories yet.</p>
          <p class="text-body-2">
            The AI will remember important information you share during conversations.
          </p>
        </div>

        <v-list
          v-else
          lines="two"
        >
          <template
            v-for="[type, typeMemories] in groupedMemories"
            :key="type"
          >
            <v-list-subheader class="text-uppercase font-weight-bold">
              {{ type }}
            </v-list-subheader>

            <v-list-item
              v-for="memory in typeMemories"
              :key="memory.id"
              class="memory-item"
            >
              <template #prepend>
                <v-avatar
                  color="primary"
                  variant="tonal"
                  size="36"
                >
                  <v-icon
                    icon="mdi-tag"
                    size="small"
                  />
                </v-avatar>
              </template>

              <v-list-item-title class="font-weight-medium">
                {{ memory.key }}
              </v-list-item-title>

              <v-list-item-subtitle v-if="editingMemory?.id !== memory.id">
                {{ memory.value }}
              </v-list-item-subtitle>

              <div
                v-if="editingMemory?.id === memory.id"
                class="mt-2"
              >
                <v-textarea
                  v-model="editValue"
                  density="compact"
                  variant="outlined"
                  rows="2"
                  auto-grow
                  hide-details
                />
                <div class="d-flex ga-2 mt-2">
                  <v-btn
                    color="primary"
                    size="small"
                    @click="saveEdit"
                  >
                    Save
                  </v-btn>
                  <v-btn
                    variant="text"
                    size="small"
                    @click="cancelEditing"
                  >
                    Cancel
                  </v-btn>
                </div>
              </div>

              <template #append>
                <div
                  v-if="editingMemory?.id !== memory.id"
                  class="d-flex ga-1"
                >
                  <v-btn
                    icon="mdi-pencil"
                    variant="text"
                    size="small"
                    @click="startEditing(memory)"
                  />
                  <v-btn
                    icon="mdi-delete"
                    variant="text"
                    size="small"
                    color="error"
                    @click="deleteMemory(memory)"
                  />
                </div>
              </template>
            </v-list-item>
          </template>
        </v-list>
      </v-card-text>

      <v-divider />

      <v-card-actions>
        <v-spacer />
        <v-btn
          variant="text"
          @click="isOpen = false"
        >
          Close
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style scoped>
.memory-item {
  padding-top: 8px;
  padding-bottom: 8px;
}
</style>
