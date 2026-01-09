<script setup lang="ts">
import { ref, computed } from 'vue'
import draggable from 'vuedraggable'
import type { KanbanColumn, KanbanTask } from '@js/types/kanban'
import KanbanTaskCard from '@js/components/kanban/KanbanTask.vue'
import KanbanTaskDialog from '@js/components/kanban/KanbanTaskDialog.vue'
import axios from '@js/common/axios'
import { error as showError, success as showSuccess } from '@js/common/snackbar'
import { confirmDialog } from '@js/common/confirm'

interface Props {
  column: KanbanColumn
  boardId: number
}

const props = defineProps<Props>()

const emit = defineEmits<{
  update: [column: KanbanColumn]
  delete: [column: KanbanColumn]
  taskMoved: [event: TaskMovedEvent]
  refresh: []
}>()

interface TaskMovedEvent {
  taskId: number
  targetColumnId: number
  position: number
}

const tasks = ref<KanbanTask[]>(props.column.tasks || [])
const showTaskDialog = ref(false)
const editingTask = ref<KanbanTask | null>(null)

const headerColor = computed(() => props.column.color || undefined)

function openAddTaskDialog(): void {
  editingTask.value = null
  showTaskDialog.value = true
}

function openEditTaskDialog(task: KanbanTask): void {
  editingTask.value = task
  showTaskDialog.value = true
}

function handleTaskSaved(savedTask: KanbanTask): void {
  const existingIndex = tasks.value.findIndex(t => t.id === savedTask.id)

  if (existingIndex >= 0) {
    tasks.value[existingIndex] = savedTask
  } else {
    tasks.value.push(savedTask)
  }
}

async function handleDeleteTask(task: KanbanTask): Promise<void> {
  const confirmed = await confirmDialog({
    title: 'Delete Task',
    message: `Are you sure you want to delete "${task.title}"?`,
    confirmButtonText: 'Delete',
    confirmButtonColor: 'error',
  })

  if (!confirmed) return

  try {
    await axios.delete(`/api/kanban/tasks/${task.id}`)
    tasks.value = tasks.value.filter(t => t.id !== task.id)
    showSuccess('Task deleted')
  } catch (err) {
    showError('Failed to delete task')
    console.error(err)
  }
}

async function handleTaskDragEnd(event: { oldIndex: number; newIndex: number; from: HTMLElement; to: HTMLElement; item: { dataset: { taskId: string } } }): Promise<void> {
  const taskId = parseInt(event.item.dataset.taskId)
  const newPosition = event.newIndex
  const targetColumnId = props.column.id

  try {
    await axios.patch(`/api/kanban/tasks/${taskId}/move`, {
      kanban_column_id: targetColumnId,
      position: newPosition,
    })
  } catch (err) {
    showError('Failed to move task')
    console.error(err)
    emit('refresh')
  }
}

function handleEditColumn(): void {
  emit('update', props.column)
}

async function handleDeleteColumn(): Promise<void> {
  const confirmed = await confirmDialog({
    title: 'Delete Column',
    message: `Are you sure you want to delete "${props.column.name}"? All tasks in this column will be deleted.`,
    confirmButtonText: 'Delete',
    confirmButtonColor: 'error',
  })

  if (!confirmed) return

  emit('delete', props.column)
}

// Expose tasks for external updates (e.g., when task moved from another column)
defineExpose({
  tasks,
  addTask(task: KanbanTask): void {
    tasks.value.push(task)
  },
  removeTask(taskId: number): void {
    tasks.value = tasks.value.filter(t => t.id !== taskId)
  },
})
</script>

<template>
  <div class="kanban-column">
    <v-card
      class="kanban-column-card"
      variant="outlined"
    >
      <v-card-title
        class="kanban-column-header d-flex align-center justify-space-between py-2 px-3"
        :style="headerColor ? { borderTop: `3px solid ${headerColor}` } : {}"
      >
        <span class="font-weight-medium">{{ column.name }}</span>
        <div>
          <v-btn
            icon="mdi-pencil-outline"
            size="x-small"
            variant="text"
            @click="handleEditColumn"
          />
          <v-btn
            icon="mdi-delete-outline"
            size="x-small"
            variant="text"
            color="error"
            @click="handleDeleteColumn"
          />
        </div>
      </v-card-title>

      <v-card-text class="kanban-column-body pa-2">
        <draggable
          v-model="tasks"
          group="tasks"
          item-key="id"
          class="kanban-task-list"
          :data-column-id="column.id"
          @end="handleTaskDragEnd"
        >
          <template #item="{ element }">
            <div :data-task-id="element.id">
              <KanbanTaskCard
                :task="element"
                @click="openEditTaskDialog(element)"
                @delete="handleDeleteTask(element)"
              />
            </div>
          </template>
        </draggable>

        <v-btn
          block
          variant="text"
          color="primary"
          prepend-icon="mdi-plus"
          class="mt-2"
          @click="openAddTaskDialog"
        >
          Add Task
        </v-btn>
      </v-card-text>
    </v-card>

    <KanbanTaskDialog
      v-model="showTaskDialog"
      :task="editingTask"
      :column-id="column.id"
      @save="handleTaskSaved"
    />
  </div>
</template>

<style scoped>
.kanban-column {
  flex-shrink: 0;
  width: 300px;
}

.kanban-column-card {
  background-color: rgb(var(--v-theme-surface));
  height: 100%;
  display: flex;
  flex-direction: column;
}

.kanban-column-header {
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.kanban-column-body {
  flex: 1;
  overflow-y: auto;
  max-height: calc(100vh - 300px);
}

.kanban-task-list {
  min-height: 50px;
}
</style>
