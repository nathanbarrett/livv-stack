<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { useDisplay } from 'vuetify'
import type {
  KanbanTask,
  KanbanTaskNote,
  KanbanTaskAttachment,
  KanbanTaskDependency,
  KanbanColumn,
  CreateTaskRequest,
  UpdateTaskRequest,
  BoardTasksResponse,
  TaskNotesResponse,
  AttachmentsResponse,
} from '@js/types/kanban'
import { useMarkdown } from '@js/composables/useMarkdown'
import KanbanTaskNotes from '@js/components/kanban/KanbanTaskNotes.vue'
import axios from '@js/common/axios'
import { error as showError, success as showSuccess } from '@js/common/snackbar'

type TaskPriority = 'low' | 'medium' | 'high'
type ContentTab = 'description' | 'plans' | 'notes' | 'attachments'

interface Props {
  modelValue: boolean
  task?: KanbanTask | null
  columnId: number
  boardId: number
  columns?: KanbanColumn[]
}

const props = defineProps<Props>()

interface TaskMovedEvent {
  task: KanbanTask
  fromColumnId: number
  toColumnId: number
}

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  save: [task: KanbanTask]
  'notes-changed': [taskId: number, notes: KanbanTaskNote[]]
  'task-moved': [event: TaskMovedEvent]
}>()

const { smAndDown } = useDisplay()
const { renderMarkdown } = useMarkdown()

const loading = ref(false)
const loadingDependencies = ref(false)
const loadingNotes = ref(false)
const title = ref('')
const description = ref('')
const implementationPlans = ref('')
const localNotes = ref<KanbanTaskNote[]>([])
const dueDate = ref<Date | null>(null)
const priority = ref<TaskPriority | null>(null)
const selectedDependencyIds = ref<number[]>([])
const availableTasks = ref<KanbanTaskDependency[]>([])
const activeTab = ref<ContentTab>('description')
const isPreviewMode = ref(false)
const showDatePicker = ref(false)
const selectedColumnId = ref<number | null>(null)
const links = ref<string[]>([])
const newLinkInput = ref('')
const localAttachments = ref<KanbanTaskAttachment[]>([])
const loadingAttachments = ref(false)
const uploadingAttachment = ref(false)
const fileInputRef = ref<HTMLInputElement | null>(null)
const thumbnailUrls = ref<Record<number, string>>({})

// Helper to convert Date to ISO string for API
function formatDateForApi(date: Date | null): string | undefined {
  if (!date) return undefined
  return date.toISOString().split('T')[0]
}

// Helper to parse ISO string to Date
function parseDateFromApi(dateStr: string | null | undefined): Date | null {
  if (!dateStr) return null
  return new Date(dateStr)
}

// Format date for display
const formattedDueDate = computed(() => {
  if (!dueDate.value) return ''
  return dueDate.value.toLocaleDateString()
})

function clearDueDate(): void {
  dueDate.value = null
}

const isEditMode = computed(() => !!props.task)
const dialogTitle = computed(() => (isEditMode.value ? 'Edit Task' : 'New Task'))

const priorityOptions = [
  { title: 'Low', value: 'low' },
  { title: 'Medium', value: 'medium' },
  { title: 'High', value: 'high' },
]

const dependencyOptions = computed(() => {
  return availableTasks.value.filter((t) => t.id !== props.task?.id)
})

const moveToColumnOptions = computed(() => {
  if (!props.columns) return []
  return props.columns.filter((c) => c.id !== props.columnId)
})

const currentTabContent = computed(() => {
  switch (activeTab.value) {
    case 'description':
      return description.value
    case 'plans':
      return implementationPlans.value
    default:
      return ''
  }
})

const renderedContent = computed(() => {
  const content = currentTabContent.value
  if (!content) return '<p class="text-medium-emphasis">No content</p>'
  return renderMarkdown(content)
})

const isNotesTab = computed(() => activeTab.value === 'notes')
const isAttachmentsTab = computed(() => activeTab.value === 'attachments')

watch(
  () => props.modelValue,
  async (open) => {
    if (open) {
      selectedColumnId.value = null
      if (props.task) {
        title.value = props.task.title
        description.value = props.task.description || ''
        implementationPlans.value = props.task.implementation_plans || ''
        localNotes.value = []
        dueDate.value = parseDateFromApi(props.task.due_date)
        priority.value = props.task.priority || null
        selectedDependencyIds.value = props.task.dependencies?.map((d) => d.id) || []
        links.value = props.task.links ? [...props.task.links] : []
        await Promise.all([
          loadAvailableTasks(),
          fetchNotes(props.task.id),
          fetchAttachments(props.task.id),
        ])
      } else {
        resetForm()
        await loadAvailableTasks()
      }
    }
  }
)

function resetForm(): void {
  title.value = ''
  description.value = ''
  implementationPlans.value = ''
  localNotes.value = []
  dueDate.value = null
  priority.value = null
  selectedDependencyIds.value = []
  selectedColumnId.value = null
  links.value = []
  newLinkInput.value = ''
  localAttachments.value = []
  thumbnailUrls.value = {}
  activeTab.value = 'description'
  isPreviewMode.value = false
  showDatePicker.value = false
}

function close(): void {
  emit('update:modelValue', false)
  resetForm()
}

async function loadAvailableTasks(): Promise<void> {
  loadingDependencies.value = true
  try {
    const response = await axios.get<BoardTasksResponse>(
      `/api/kanban/boards/${props.boardId}/tasks`
    )
    availableTasks.value = response.data.tasks
  } catch (err) {
    console.error('Failed to load tasks for dependencies', err)
    availableTasks.value = []
  } finally {
    loadingDependencies.value = false
  }
}

async function fetchNotes(taskId: number): Promise<void> {
  loadingNotes.value = true
  try {
    const response = await axios.get<TaskNotesResponse>(
      `/api/kanban/tasks/${taskId}/notes`
    )
    localNotes.value = response.data.notes
  } catch (err) {
    console.error('Failed to load notes', err)
    localNotes.value = []
  } finally {
    loadingNotes.value = false
  }
}

async function fetchAttachments(taskId: number): Promise<void> {
  loadingAttachments.value = true
  try {
    const response = await axios.get<AttachmentsResponse>(
      `/api/kanban/tasks/${taskId}/attachments`
    )
    localAttachments.value = response.data.attachments
  } catch (err) {
    console.error('Failed to load attachments', err)
    localAttachments.value = []
  } finally {
    loadingAttachments.value = false
  }
}

async function uploadAttachment(event: Event): Promise<void> {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]

  if (!file || !props.task) {
    return
  }

  if (file.size > 10 * 1024 * 1024) {
    showError('File must be less than 10MB')
    return
  }

  if (localAttachments.value.length >= 10) {
    showError('Maximum 10 attachments per task')
    return
  }

  uploadingAttachment.value = true
  try {
    const formData = new FormData()
    formData.append('file', file)

    const response = await axios.post(
      `/api/kanban/tasks/${props.task.id}/attachments`,
      formData,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    )
    localAttachments.value.push(response.data.attachment)
    showSuccess('Attachment uploaded')
  } catch (err) {
    showError('Failed to upload attachment')
    console.error(err)
  } finally {
    uploadingAttachment.value = false
    input.value = ''
  }
}

async function deleteAttachment(attachmentId: number): Promise<void> {
  try {
    await axios.delete(`/api/kanban/attachments/${attachmentId}`)
    localAttachments.value = localAttachments.value.filter((a) => a.id !== attachmentId)
    delete thumbnailUrls.value[attachmentId]
    showSuccess('Attachment deleted')
  } catch (err) {
    showError('Failed to delete attachment')
    console.error(err)
  }
}

async function viewAttachment(attachment: KanbanTaskAttachment): Promise<void> {
  try {
    const response = await axios.get(`/api/kanban/attachments/${attachment.id}`)
    window.open(response.data.attachment.url, '_blank')
  } catch (err) {
    showError('Failed to get attachment URL')
    console.error(err)
  }
}

async function loadThumbnailUrl(attachment: KanbanTaskAttachment): Promise<void> {
  if (thumbnailUrls.value[attachment.id]) {
    return
  }

  try {
    const response = await axios.get(`/api/kanban/attachments/${attachment.id}`)
    thumbnailUrls.value[attachment.id] = response.data.attachment.url
  } catch (err) {
    console.error('Failed to load thumbnail', err)
  }
}

function formatFileSize(bytes: number): string {
  if (bytes < 1024) {
    return `${bytes} B`
  }

  if (bytes < 1024 * 1024) {
    return `${(bytes / 1024).toFixed(1)} KB`
  }

  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

function updateCurrentTabContent(value: string): void {
  switch (activeTab.value) {
    case 'description':
      description.value = value
      break
    case 'plans':
      implementationPlans.value = value
      break
  }
}

function handleNoteAdded(note: KanbanTaskNote): void {
  localNotes.value.push(note)
  if (props.task) {
    emit('notes-changed', props.task.id, [...localNotes.value])
  }
}

function handleNoteDeleted(noteId: number): void {
  localNotes.value = localNotes.value.filter((n) => n.id !== noteId)
  if (props.task) {
    emit('notes-changed', props.task.id, [...localNotes.value])
  }
}

function addLink(): void {
  const url = newLinkInput.value.trim()
  if (!url) return

  try {
    new URL(url)
    if (!links.value.includes(url)) {
      links.value.push(url)
    }
    newLinkInput.value = ''
  } catch {
    showError('Please enter a valid URL')
  }
}

function removeLink(index: number): void {
  links.value.splice(index, 1)
}

function truncateUrl(url: string): string {
  try {
    const parsed = new URL(url)
    const display = parsed.hostname + (parsed.pathname !== '/' ? parsed.pathname : '')
    return display.length > 40 ? display.substring(0, 40) + '...' : display
  } catch {
    return url.length > 40 ? url.substring(0, 40) + '...' : url
  }
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
        implementation_plans: implementationPlans.value || undefined,
        due_date: formatDateForApi(dueDate.value) ?? null,
        priority: priority.value,
        dependency_ids: selectedDependencyIds.value,
        links: links.value.length > 0 ? links.value : null,
      }

      const response = await axios.patch(`/api/kanban/tasks/${props.task.id}`, payload)
      const updatedTask: KanbanTask = response.data.task

      // Check if we need to move the task to a different column
      if (selectedColumnId.value && selectedColumnId.value !== props.columnId) {
        await axios.patch(`/api/kanban/tasks/${props.task.id}/move`, {
          kanban_column_id: selectedColumnId.value,
          position: 0, // Move to top of target column
        })
        updatedTask.kanban_column_id = selectedColumnId.value
        showSuccess('Task updated and moved')
        emit('task-moved', {
          task: updatedTask,
          fromColumnId: props.columnId,
          toColumnId: selectedColumnId.value,
        })
      } else {
        showSuccess('Task updated')
        emit('save', updatedTask)
      }
    } else {
      const payload: CreateTaskRequest = {
        title: title.value,
        description: description.value || undefined,
        implementation_plans: implementationPlans.value || undefined,
        due_date: formatDateForApi(dueDate.value),
        priority: priority.value || undefined,
        dependency_ids: selectedDependencyIds.value,
        links: links.value.length > 0 ? links.value : undefined,
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
    :max-width="smAndDown ? '100%' : '800'"
    :fullscreen="smAndDown"
    scrollable
    @update:model-value="emit('update:modelValue', $event)"
  >
    <v-card>
      <v-card-title class="d-flex justify-space-between align-center flex-wrap ga-2">
        <span>{{ dialogTitle }}</span>
        <v-chip v-if="task" size="small" variant="tonal" color="grey">
          #{{ task.id }}
        </v-chip>
      </v-card-title>

      <v-card-text>
        <v-text-field
          v-model="title"
          label="Title"
          variant="outlined"
          density="comfortable"
          :rules="[(v: string) => !!v || 'Title is required']"
          class="mb-3"
        />

        <div class="d-flex justify-space-between align-center mb-2">
          <v-tabs v-model="activeTab" density="compact">
            <v-tab value="description">Description</v-tab>
            <v-tab value="plans">Implementation Plans</v-tab>
            <v-tab value="notes">Notes</v-tab>
            <v-tab value="attachments">Attachments</v-tab>
          </v-tabs>

          <v-btn-toggle
            v-if="!isNotesTab && !isAttachmentsTab"
            v-model="isPreviewMode"
            mandatory
            density="compact"
            variant="outlined"
          >
            <v-btn :value="false" size="small">Edit</v-btn>
            <v-btn :value="true" size="small">Preview</v-btn>
          </v-btn-toggle>
        </div>

        <div class="content-area mb-4">
          <template v-if="isNotesTab">
            <div v-if="loadingNotes" class="d-flex justify-center align-center py-8">
              <v-progress-circular indeterminate color="primary" />
            </div>
            <KanbanTaskNotes
              v-else-if="task"
              :task-id="task.id"
              :notes="localNotes"
              @note-added="handleNoteAdded"
              @note-deleted="handleNoteDeleted"
            />
            <v-alert v-else type="info" variant="tonal">
              Save the task first to add notes.
            </v-alert>
          </template>
          <template v-else-if="isAttachmentsTab">
            <div v-if="loadingAttachments" class="d-flex justify-center align-center py-8">
              <v-progress-circular indeterminate color="primary" />
            </div>
            <div v-else-if="task">
              <input
                ref="fileInputRef"
                type="file"
                hidden
                @change="uploadAttachment"
              />
              <v-btn
                color="primary"
                variant="tonal"
                :loading="uploadingAttachment"
                :disabled="localAttachments.length >= 10"
                class="mb-4"
                @click="fileInputRef?.click()"
              >
                <v-icon start>mdi-upload</v-icon>
                Upload File
              </v-btn>
              <div class="text-caption text-medium-emphasis mb-4">
                {{ localAttachments.length }}/10 files (max 10MB each)
              </div>

              <v-list v-if="localAttachments.length > 0">
                <v-list-item
                  v-for="attachment in localAttachments"
                  :key="attachment.id"
                  class="px-0"
                >
                  <template #prepend>
                    <v-avatar
                      v-if="attachment.mime_type.startsWith('image/')"
                      rounded
                      size="48"
                      class="mr-3"
                    >
                      <v-img
                        v-if="thumbnailUrls[attachment.id]"
                        :src="thumbnailUrls[attachment.id]"
                      />
                      <v-icon v-else @vue:mounted="loadThumbnailUrl(attachment)">
                        mdi-image
                      </v-icon>
                    </v-avatar>
                    <v-avatar
                      v-else-if="attachment.mime_type.startsWith('video/')"
                      rounded
                      size="48"
                      color="grey-darken-3"
                      class="mr-3"
                    >
                      <v-icon>mdi-video</v-icon>
                    </v-avatar>
                    <v-avatar v-else rounded size="48" color="grey-darken-3" class="mr-3">
                      <v-icon>mdi-file</v-icon>
                    </v-avatar>
                  </template>

                  <v-list-item-title>{{ attachment.original_filename }}</v-list-item-title>
                  <v-list-item-subtitle>{{ formatFileSize(attachment.size) }}</v-list-item-subtitle>

                  <template #append>
                    <v-btn
                      icon="mdi-eye"
                      variant="text"
                      size="small"
                      @click="viewAttachment(attachment)"
                    />
                    <v-btn
                      icon="mdi-delete"
                      variant="text"
                      size="small"
                      color="error"
                      @click="deleteAttachment(attachment.id)"
                    />
                  </template>
                </v-list-item>
              </v-list>
              <v-alert v-else type="info" variant="tonal">
                No attachments yet
              </v-alert>
            </div>
            <v-alert v-else type="info" variant="tonal">
              Save the task first to add attachments.
            </v-alert>
          </template>
          <template v-else>
            <v-textarea
              v-if="!isPreviewMode"
              :model-value="currentTabContent"
              :label="`${activeTab === 'description' ? 'Description' : 'Implementation Plans'} (Markdown)`"
              variant="outlined"
              density="comfortable"
              rows="8"
              auto-grow
              @update:model-value="updateCurrentTabContent"
            />
            <div
              v-else
              class="markdown-preview pa-4 border rounded"
              v-html="renderedContent"
            />
          </template>
        </div>

        <v-row>
          <v-col :cols="smAndDown ? 12 : 6">
            <v-menu
              v-model="showDatePicker"
              :close-on-content-click="false"
              location="bottom"
            >
              <template #activator="{ props: menuProps }">
                <v-text-field
                  v-bind="menuProps"
                  :model-value="formattedDueDate"
                  label="Due Date"
                  variant="outlined"
                  density="comfortable"
                  readonly
                  placeholder="Click to select date"
                  prepend-inner-icon="mdi-calendar"
                >
                  <template v-if="dueDate" #append-inner>
                    <v-icon
                      icon="mdi-close"
                      size="small"
                      @click.stop="clearDueDate"
                    />
                  </template>
                </v-text-field>
              </template>

              <v-date-picker
                v-model="dueDate"
                @update:model-value="showDatePicker = false"
              />
            </v-menu>
          </v-col>
          <v-col :cols="smAndDown ? 12 : 6">
            <v-select
              v-model="priority"
              :items="priorityOptions"
              label="Priority"
              variant="outlined"
              density="comfortable"
              clearable
            />
          </v-col>
        </v-row>

        <v-select
          v-if="isEditMode && moveToColumnOptions.length > 0"
          v-model="selectedColumnId"
          :items="moveToColumnOptions"
          item-title="name"
          item-value="id"
          label="Move to Column"
          variant="outlined"
          density="comfortable"
          clearable
          placeholder="Keep in current column"
          class="mt-2"
        />

        <v-autocomplete
          v-model="selectedDependencyIds"
          :items="dependencyOptions"
          item-title="title"
          item-value="id"
          label="Dependencies (tasks this depends on)"
          variant="outlined"
          density="comfortable"
          chips
          closable-chips
          multiple
          :loading="loadingDependencies"
          class="mt-2"
        >
          <template #chip="{ props: chipProps, item }">
            <v-chip v-bind="chipProps" size="small">
              #{{ item.raw.id }} {{ item.raw.title }}
            </v-chip>
          </template>
          <template #item="{ props: itemProps, item }">
            <v-list-item v-bind="itemProps">
              <template #prepend>
                <v-chip size="x-small" variant="tonal" class="mr-2">
                  #{{ item.raw.id }}
                </v-chip>
              </template>
            </v-list-item>
          </template>
        </v-autocomplete>

        <div class="mt-4">
          <div class="text-subtitle-2 mb-2">Links</div>

          <div class="d-flex ga-2 mb-2">
            <v-text-field
              v-model="newLinkInput"
              label="Add URL"
              variant="outlined"
              density="compact"
              placeholder="https://..."
              hide-details
              @keyup.enter="addLink"
            />
            <v-btn icon="mdi-plus" variant="tonal" @click="addLink" />
          </div>

          <v-chip-group v-if="links.length > 0" column>
            <v-chip
              v-for="(link, index) in links"
              :key="index"
              closable
              @click:close="removeLink(index)"
            >
              <a
                v-if="isPreviewMode"
                :href="link"
                target="_blank"
                rel="noopener noreferrer"
                class="text-decoration-none"
                @click.stop
              >
                {{ truncateUrl(link) }}
              </a>
              <span v-else>{{ truncateUrl(link) }}</span>
            </v-chip>
          </v-chip-group>
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
.markdown-preview {
  min-height: 200px;
  background-color: rgb(var(--v-theme-surface));
}

.markdown-preview :deep(pre) {
  background-color: rgb(var(--v-theme-surface-variant));
  padding: 1rem;
  border-radius: 4px;
  overflow-x: auto;
}

.markdown-preview :deep(code) {
  font-family: monospace;
}

.markdown-preview :deep(p) {
  margin-bottom: 0.5rem;
}

.markdown-preview :deep(ul),
.markdown-preview :deep(ol) {
  padding-left: 1.5rem;
  margin-bottom: 0.5rem;
}

.markdown-preview :deep(h1),
.markdown-preview :deep(h2),
.markdown-preview :deep(h3),
.markdown-preview :deep(h4) {
  margin-top: 1rem;
  margin-bottom: 0.5rem;
}

.content-area {
  min-height: 250px;
}
</style>
