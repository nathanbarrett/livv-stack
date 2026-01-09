<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@js/Pages/AppLayout.vue'
import KanbanBoard from '@js/components/kanban/KanbanBoard.vue'
import type { KanbanBoard as KanbanBoardType, CreateBoardRequest } from '@js/types/kanban'
import axios from '@js/common/axios'
import { error as showError, success as showSuccess } from '@js/common/snackbar'
import { usePage } from '@inertiajs/vue3'
import type { User } from '@js/types/models'

const user = computed<User | null>(() => usePage().props.auth.user)

const boards = ref<KanbanBoardType[]>([])
const selectedBoardId = ref<number | null>(null)
const loading = ref(true)
const showCreateBoardDialog = ref(false)
const newBoardName = ref('')
const newBoardDescription = ref('')
const creatingBoard = ref(false)

async function fetchBoards(): Promise<void> {
  loading.value = true

  try {
    const response = await axios.get('/api/kanban/boards')
    boards.value = response.data.boards

    if (boards.value.length > 0 && !selectedBoardId.value) {
      selectedBoardId.value = boards.value[0].id
    }
  } catch (err) {
    showError('Failed to load boards')
    console.error(err)
  } finally {
    loading.value = false
  }
}

async function createBoard(): Promise<void> {
  if (!newBoardName.value.trim()) {
    showError('Board name is required')
    return
  }

  creatingBoard.value = true

  try {
    const payload: CreateBoardRequest = {
      name: newBoardName.value,
      description: newBoardDescription.value || undefined,
    }

    const response = await axios.post('/api/kanban/boards', payload)
    boards.value.push(response.data.board)
    selectedBoardId.value = response.data.board.id
    showSuccess('Board created')
    showCreateBoardDialog.value = false
    newBoardName.value = ''
    newBoardDescription.value = ''
  } catch (err) {
    showError('Failed to create board')
    console.error(err)
  } finally {
    creatingBoard.value = false
  }
}

onMounted(() => {
  if (user.value) {
    fetchBoards()
  }
})
</script>

<template>
  <AppLayout>
    <v-container
      fluid
      class="pa-4"
    >
      <template v-if="!user">
        <v-alert
          type="warning"
          variant="tonal"
        >
          Please log in to access your Kanban boards.
        </v-alert>
      </template>

      <template v-else>
        <div
          v-if="loading"
          class="d-flex justify-center align-center pa-8"
        >
          <v-progress-circular
            indeterminate
            color="primary"
          />
        </div>

        <template v-else>
          <div class="d-flex align-center justify-space-between mb-4">
            <div class="d-flex align-center ga-4">
              <v-select
                v-if="boards.length > 0"
                v-model="selectedBoardId"
                :items="boards"
                item-title="name"
                item-value="id"
                label="Select Board"
                variant="outlined"
                density="comfortable"
                hide-details
                style="min-width: 250px"
              />
              <span
                v-else
                class="text-medium-emphasis"
              >No boards yet</span>
            </div>

            <v-btn
              color="primary"
              prepend-icon="mdi-plus"
              @click="showCreateBoardDialog = true"
            >
              New Board
            </v-btn>
          </div>

          <KanbanBoard
            v-if="selectedBoardId"
            :key="selectedBoardId"
            :board-id="selectedBoardId"
          />

          <v-alert
            v-else
            type="info"
            variant="tonal"
          >
            Create a new board to get started!
          </v-alert>
        </template>
      </template>
    </v-container>

    <v-dialog
      v-model="showCreateBoardDialog"
      max-width="450"
    >
      <v-card>
        <v-card-title>Create New Board</v-card-title>

        <v-card-text>
          <v-text-field
            v-model="newBoardName"
            label="Board Name"
            variant="outlined"
            density="comfortable"
            :rules="[(v: string) => !!v || 'Name is required']"
            class="mb-3"
          />

          <v-textarea
            v-model="newBoardDescription"
            label="Description (optional)"
            variant="outlined"
            density="comfortable"
            rows="2"
          />
        </v-card-text>

        <v-card-actions>
          <v-spacer />
          <v-btn
            variant="outlined"
            @click="showCreateBoardDialog = false"
          >
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            variant="flat"
            :loading="creatingBoard"
            @click="createBoard"
          >
            Create
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </AppLayout>
</template>

<style scoped>
.kanban-page {
  min-height: calc(100vh - 64px);
}
</style>
