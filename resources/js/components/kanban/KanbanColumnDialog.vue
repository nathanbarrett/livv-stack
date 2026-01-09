<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import type { KanbanColumn, CreateColumnRequest, UpdateColumnRequest } from '@js/types/kanban'
import axios from '@js/common/axios'
import { error as showError, success as showSuccess } from '@js/common/snackbar'

interface Props {
  modelValue: boolean
  column?: KanbanColumn | null
  boardId: number
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  save: [column: KanbanColumn]
}>()

const loading = ref(false)
const name = ref('')
const color = ref<string | null>(null)

const isEditMode = computed(() => !!props.column)
const dialogTitle = computed(() => isEditMode.value ? 'Edit Column' : 'New Column')

watch(() => props.modelValue, (open) => {
  if (open) {
    if (props.column) {
      name.value = props.column.name
      color.value = props.column.color || null
    } else {
      resetForm()
    }
  }
})

function resetForm(): void {
  name.value = ''
  color.value = null
}

function close(): void {
  emit('update:modelValue', false)
  resetForm()
}

async function save(): Promise<void> {
  if (!name.value.trim()) {
    showError('Name is required')
    return
  }

  loading.value = true

  try {
    if (isEditMode.value && props.column) {
      const payload: UpdateColumnRequest = {
        name: name.value,
        color: color.value || undefined,
      }

      const response = await axios.patch(`/api/kanban/columns/${props.column.id}`, payload)
      showSuccess('Column updated')
      emit('save', response.data.column)
    } else {
      const payload: CreateColumnRequest = {
        name: name.value,
        color: color.value || undefined,
      }

      const response = await axios.post(`/api/kanban/boards/${props.boardId}/columns`, payload)
      showSuccess('Column created')
      emit('save', response.data.column)
    }

    close()
  } catch (err) {
    showError('Failed to save column')
    console.error(err)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <v-dialog
    :model-value="modelValue"
    max-width="400"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <v-card>
      <v-card-title>{{ dialogTitle }}</v-card-title>

      <v-card-text>
        <v-text-field
          v-model="name"
          label="Column Name"
          variant="outlined"
          density="comfortable"
          :rules="[(v: string) => !!v || 'Name is required']"
          class="mb-3"
        />

        <v-text-field
          v-model="color"
          label="Color (hex)"
          variant="outlined"
          density="comfortable"
          placeholder="#ff0000"
          prepend-inner-icon="mdi-palette"
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
