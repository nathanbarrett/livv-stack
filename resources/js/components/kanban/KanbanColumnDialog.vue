<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { useDisplay } from 'vuetify'
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

const { smAndDown } = useDisplay()

const loading = ref(false)
const name = ref('')
const color = ref<string | null>(null)
const showColorPicker = ref(false)

const isEditMode = computed(() => !!props.column)
const dialogTitle = computed(() => (isEditMode.value ? 'Edit Column' : 'New Column'))

// Computed for color picker - provides a default when color is null and handles object/string formats
const pickerColor = computed({
  get: () => color.value || '#1976D2',
  set: (val: string | Record<string, unknown> | null) => {
    if (typeof val === 'string') {
      color.value = val
    } else if (val && typeof val === 'object') {
      // Handle RGBA/HSLA object format - extract hex value
      if ('hexa' in val && typeof val.hexa === 'string') {
        color.value = val.hexa
      } else if ('hex' in val && typeof val.hex === 'string') {
        color.value = val.hex
      }
    }
  }
})

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      if (props.column) {
        name.value = props.column.name
        color.value = props.column.color || null
      } else {
        resetForm()
      }
    }
  }
)

function resetForm(): void {
  name.value = ''
  color.value = null
  showColorPicker.value = false
}

function close(): void {
  emit('update:modelValue', false)
  resetForm()
}

function clearColor(): void {
  color.value = null
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
    :max-width="smAndDown ? '100%' : '400'"
    :fullscreen="smAndDown"
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

        <div class="d-flex align-center ga-2">
          <v-menu v-model="showColorPicker" :close-on-content-click="false" location="bottom">
            <template #activator="{ props: menuProps }">
              <v-text-field
                v-bind="menuProps"
                :model-value="color || ''"
                label="Column Color"
                variant="outlined"
                density="comfortable"
                readonly
                placeholder="Click to select color"
                class="flex-grow-1"
              >
                <template #prepend-inner>
                  <div
                    v-if="color"
                    class="color-preview mr-2"
                    :style="{ backgroundColor: color }"
                  />
                  <v-icon v-else icon="mdi-palette" />
                </template>
              </v-text-field>
            </template>

            <v-card min-width="300">
              <v-color-picker
                v-model="pickerColor"
                mode="hexa"
                :modes="['hexa']"
                show-swatches
                :swatches-max-height="150"
              />
              <v-card-actions>
                <v-btn variant="text" size="small" @click="clearColor">
                  Clear
                </v-btn>
                <v-spacer />
                <v-btn variant="text" size="small" @click="showColorPicker = false">
                  Done
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-menu>

          <v-btn
            v-if="color"
            icon="mdi-close"
            size="small"
            variant="text"
            @click="clearColor"
          />
        </div>
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn variant="outlined" @click="close">
          Cancel
        </v-btn>
        <v-btn color="primary" variant="flat" :loading="loading" @click="save">
          Save
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style scoped>
.color-preview {
  width: 24px;
  height: 24px;
  border-radius: 4px;
  border: 1px solid rgba(0, 0, 0, 0.12);
}
</style>
