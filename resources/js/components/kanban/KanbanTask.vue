<script setup lang="ts">
import type { KanbanTask } from '@js/types/kanban'

interface Props {
  task: KanbanTask
}

defineProps<Props>()

const emit = defineEmits<{
  click: [task: KanbanTask]
  delete: [task: KanbanTask]
}>()

function getPriorityColor(priority: string | null | undefined): string {
  switch (priority) {
    case 'high':
      return 'error'
    case 'medium':
      return 'warning'
    case 'low':
      return 'success'
    default:
      return 'grey'
  }
}

function formatDate(dateString: string | null | undefined): string {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString()
}
</script>

<template>
  <v-card
    class="kanban-task mb-2"
    variant="outlined"
    @click="emit('click', task)"
  >
    <v-card-text class="pa-3">
      <div class="d-flex justify-space-between align-start">
        <div class="task-title font-weight-medium">
          {{ task.title }}
        </div>
        <v-btn
          icon="mdi-delete-outline"
          size="x-small"
          variant="text"
          color="error"
          @click.stop="emit('delete', task)"
        />
      </div>

      <div
        v-if="task.description"
        class="task-description text-body-2 text-medium-emphasis mt-1"
      >
        {{ task.description.length > 80 ? task.description.substring(0, 80) + '...' : task.description }}
      </div>

      <div class="d-flex align-center mt-2 ga-2">
        <v-chip
          v-if="task.priority"
          :color="getPriorityColor(task.priority)"
          size="x-small"
          variant="flat"
        >
          {{ task.priority }}
        </v-chip>

        <v-chip
          v-if="task.due_date"
          size="x-small"
          variant="outlined"
          prepend-icon="mdi-calendar"
        >
          {{ formatDate(task.due_date) }}
        </v-chip>
      </div>
    </v-card-text>
  </v-card>
</template>

<style scoped>
.kanban-task {
  cursor: pointer;
  transition: box-shadow 0.2s ease;
}

.kanban-task:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.task-title {
  word-break: break-word;
  line-height: 1.3;
}

.task-description {
  word-break: break-word;
}
</style>
