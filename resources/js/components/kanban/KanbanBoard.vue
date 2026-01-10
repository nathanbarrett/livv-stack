<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import draggable from 'vuedraggable'
import type { KanbanBoard, KanbanColumn, KanbanTask } from '@js/types/kanban'
import KanbanColumnComponent from '@js/components/kanban/KanbanColumn.vue'
import KanbanColumnDialog from '@js/components/kanban/KanbanColumnDialog.vue'
import { useKanbanBroadcast } from '@js/composables/useKanbanBroadcast'
import axios from '@js/common/axios'
import { error as showError, success as showSuccess } from '@js/common/snackbar'

interface Props {
  boardId: number
}

const props = defineProps<Props>()

const board = ref<KanbanBoard | null>(null)
const columns = ref<KanbanColumn[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

const showColumnDialog = ref(false)
const editingColumn = ref<KanbanColumn | null>(null)

// Convert boardId prop to a ref for the broadcast composable
const boardIdRef = computed(() => props.boardId)

// Subscribe to real-time updates
const { leave: leaveBroadcast } = useKanbanBroadcast(boardIdRef, () => {
  // On any board update, refetch the entire board to stay in sync
  fetchBoard()
})

async function fetchBoard(): Promise<void> {
  loading.value = true
  error.value = null

  try {
    const response = await axios.get(`/api/kanban/boards/${props.boardId}`)
    board.value = response.data.board
    columns.value = response.data.board.columns || []
  } catch (err) {
    error.value = 'Failed to load board'
    console.error(err)
  } finally {
    loading.value = false
  }
}

function openAddColumnDialog(): void {
  editingColumn.value = null
  showColumnDialog.value = true
}

function openEditColumnDialog(column: KanbanColumn): void {
  editingColumn.value = column
  showColumnDialog.value = true
}

function handleColumnSaved(savedColumn: KanbanColumn): void {
  const existingIndex = columns.value.findIndex(c => c.id === savedColumn.id)

  if (existingIndex >= 0) {
    columns.value[existingIndex] = {
      ...columns.value[existingIndex],
      ...savedColumn,
    }
  } else {
    savedColumn.tasks = []
    columns.value.push(savedColumn)
  }
}

async function handleDeleteColumn(column: KanbanColumn): Promise<void> {
  try {
    await axios.delete(`/api/kanban/columns/${column.id}`)
    columns.value = columns.value.filter(c => c.id !== column.id)
    showSuccess('Column deleted')
  } catch (err) {
    showError('Failed to delete column')
    console.error(err)
  }
}

async function handleColumnDragEnd(event: { oldIndex: number; newIndex: number }): Promise<void> {
  const column = columns.value[event.newIndex]

  if (!column) return

  try {
    await axios.patch(`/api/kanban/columns/${column.id}/move`, {
      position: event.newIndex,
    })
  } catch (err) {
    showError('Failed to move column')
    console.error(err)
    await fetchBoard()
  }
}

interface TaskMovedFromDialogEvent {
  task: KanbanTask
  fromColumnId: number
  toColumnId: number
}

function handleTaskMovedFromDialog(event: TaskMovedFromDialogEvent): void {
  // Find the target column and add the task to the top
  const targetColumn = columns.value.find(c => c.id === event.toColumnId)
  if (targetColumn && targetColumn.tasks) {
    targetColumn.tasks.unshift(event.task)
  }
}

onMounted(() => {
  fetchBoard()
})

onUnmounted(() => {
  leaveBroadcast()
})
</script>

<template>
  <div class="kanban-board">
    <div
      v-if="loading"
      class="d-flex justify-center align-center pa-8"
    >
      <v-progress-circular
        indeterminate
        color="primary"
      />
    </div>

    <div
      v-else-if="error"
      class="d-flex justify-center align-center pa-8"
    >
      <v-alert
        type="error"
        variant="tonal"
      >
        {{ error }}
        <template #append>
          <v-btn
            variant="text"
            @click="fetchBoard"
          >
            Retry
          </v-btn>
        </template>
      </v-alert>
    </div>

    <template v-else>
      <div class="kanban-header d-flex align-center justify-space-between mb-4">
        <h2 class="text-h5">
          {{ board?.name }}
        </h2>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="openAddColumnDialog"
        >
          Add Column
        </v-btn>
      </div>

      <div class="kanban-columns-wrapper">
        <draggable
          v-model="columns"
          group="columns"
          item-key="id"
          class="kanban-columns"
          handle=".kanban-column-header"
          @end="handleColumnDragEnd"
        >
          <template #item="{ element }">
            <KanbanColumnComponent
              :key="element.id"
              :column="element"
              :board-id="boardId"
              :columns="columns"
              @update="openEditColumnDialog"
              @delete="handleDeleteColumn"
              @refresh="fetchBoard"
              @task-moved-from-dialog="handleTaskMovedFromDialog"
            />
          </template>
        </draggable>
      </div>
    </template>

    <KanbanColumnDialog
      v-model="showColumnDialog"
      :column="editingColumn"
      :board-id="boardId"
      @save="handleColumnSaved"
    />
  </div>
</template>

<style scoped>
.kanban-board {
  height: 100%;
}

.kanban-columns-wrapper {
  overflow-x: auto;
  padding-bottom: 16px;
}

.kanban-columns {
  display: flex;
  gap: 16px;
  min-height: 400px;
}
</style>
