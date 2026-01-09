<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import type { KanbanTask, CreateTaskRequest, UpdateTaskRequest } from '@js/types/kanban'
import axios from '@js/common/axios'
import { error as showError, success as showSuccess } from '@js/common/snackbar'

type TaskPriority = 'low' | 'medium' | 'high'

interface Props {
  modelValue: boolean
  task?: KanbanTask | null
  columnId: number
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  save: [task: KanbanTask]
}>()

const loading = ref(false)
const title = ref('')
const description = ref('')
const dueDate = ref<string | null>(null)
const priority = ref<TaskPriority | null>(null)

const isEditMode = computed(() => !!props.task)
const dialogTitle = computed(() => isEditMode.value ? 'Edit Task' : 'New Task')

const priorityOptions = [
  { title: 'Low', value: 'low' },
  { title: 'Medium', value: 'medium' },
  { title: 'High', value: 'high' },
]

watch(() => props.modelValue, (open) => {
  if (open) {
    if (props.task) {
      title.value = props.task.title
      description.value = props.task.description || ''
      dueDate.value = props.task.due_date ? props.task.due_date.split('T')[0] : null
      priority.value = props.task.priority || null
    } else {
      resetForm()
    }
  }
})

function resetForm(): void {
  title.value = ''
  description.value = ''
  dueDate.value = null
  priority.value = null
}

function close(): void {
  emit('update:modelValue', false)
  resetForm()
}

async function save(): Promise<void> {
  if (!title.value.trim()) {
    showError('Title is required')
    return
  }

  loading.value = true

  try {
    if (isEditMode.value && props.task) {
      const payload: UpdateTaskRequest = {
        title: title.value,
        description: description.value || undefined,
        due_date: dueDate.value || undefined,
        priority: priority.value || undefined,
      }

      const response = await axios.patch(`/api/kanban/tasks/${props.task.id}`, payload)
      showSuccess('Task updated')
      emit('save', response.data.task)
    } else {
      const payload: CreateTaskRequest = {
        title: title.value,
        description: description.value || undefined,
        due_date: dueDate.value || undefined,
        priority: priority.value || undefined,
      }

      const response = await axios.post(`/api/kanban/columns/${props.columnId}/tasks`, payload)
      showSuccess('Task created')
      emit('save', response.data.task)
    }

    close()
  } catch (err) {
    showError('Failed to save task')
    console.error(err)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <v-dialog
    :model-value="modelValue"
    max-width="500"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <v-card>
      <v-card-title>{{ dialogTitle }}</v-card-title>

      <v-card-text>
        <v-text-field
          v-model="title"
          label="Title"
          variant="outlined"
          density="comfortable"
          :rules="[(v: string) => !!v || 'Title is required']"
          class="mb-3"
        />

        <v-textarea
          v-model="description"
          label="Description"
          variant="outlined"
          density="comfortable"
          rows="3"
          class="mb-3"
        />

        <v-text-field
          v-model="dueDate"
          label="Due Date"
          type="date"
          variant="outlined"
          density="comfortable"
          class="mb-3"
        />

        <v-select
          v-model="priority"
          :items="priorityOptions"
          label="Priority"
          variant="outlined"
          density="comfortable"
          clearable
        />
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn
          variant="outlined"
          @click="close"
        >
          Cancel
        </v-btn>
        <v-btn
          color="primary"
          variant="flat"
          :loading="loading"
          @click="save"
        >
          Save
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
