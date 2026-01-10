<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import type {
  KanbanTaskNote,
  KanbanTaskNoteAuthor,
  CreateTaskNoteRequest,
  TaskNoteResponse,
} from '@js/types/kanban'
import { useMarkdown } from '@js/composables/useMarkdown'
import { useDayjs } from '@js/composables/useDayjs'
import { useColorContrast } from '@js/composables/useColorContrast'
import axios from '@js/common/axios'
import { error as showError } from '@js/common/snackbar'

interface Props {
  taskId: number
  notes: KanbanTaskNote[]
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'note-added': [note: KanbanTaskNote]
  'note-deleted': [noteId: number]
}>()

const { renderMarkdown } = useMarkdown()
const { formatDateTime } = useDayjs()
const { getMutedContrastColor, getCssVariableColor, vuetifyRgbToColor } = useColorContrast()

const newNote = ref('')
const addingNote = ref(false)
const deletingNoteId = ref<number | null>(null)
const userMetaColor = ref('rgba(255, 255, 255, 0.7)')
const aiMetaColor = ref('rgba(0, 0, 0, 0.6)')

onMounted(() => {
  // Get background colors from Vuetify CSS variables and compute contrasting meta colors
  const primaryRgb = getCssVariableColor('--v-theme-primary')
  const surfaceVariantRgb = getCssVariableColor('--v-theme-surface-variant')

  if (primaryRgb) {
    const primaryColor = vuetifyRgbToColor(primaryRgb)
    userMetaColor.value = getMutedContrastColor(primaryColor, 0.75)
  }

  if (surfaceVariantRgb) {
    const surfaceVariantColor = vuetifyRgbToColor(surfaceVariantRgb)
    aiMetaColor.value = getMutedContrastColor(surfaceVariantColor, 0.7)
  }
})

const sortedNotes = computed(() => {
  return [...props.notes].sort(
    (a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime()
  )
})

function getMetaColor(author: KanbanTaskNoteAuthor): string {
  return author === 'user' ? userMetaColor.value : aiMetaColor.value
}

async function addNote(author: KanbanTaskNoteAuthor): Promise<void> {
  if (!newNote.value.trim()) {
    return
  }

  addingNote.value = true

  try {
    const payload: CreateTaskNoteRequest = {
      note: newNote.value,
      author,
    }

    const response = await axios.post<TaskNoteResponse>(
      `/api/kanban/tasks/${props.taskId}/notes`,
      payload
    )

    emit('note-added', response.data.note)
    newNote.value = ''
  } catch (err) {
    showError('Failed to add note')
    console.error(err)
  } finally {
    addingNote.value = false
  }
}

async function deleteNote(noteId: number): Promise<void> {
  deletingNoteId.value = noteId

  try {
    await axios.delete(`/api/kanban/notes/${noteId}`)
    emit('note-deleted', noteId)
  } catch (err) {
    showError('Failed to delete note')
    console.error(err)
  } finally {
    deletingNoteId.value = null
  }
}
</script>

<template>
  <div class="notes-container d-flex flex-column h-100">
    <div class="notes-list flex-grow-1 overflow-y-auto pa-2">
      <div v-if="sortedNotes.length === 0" class="text-center text-medium-emphasis py-8">
        No notes yet. Add a note below.
      </div>

      <div
        v-for="note in sortedNotes"
        :key="note.id"
        class="note-wrapper d-flex mb-3"
        :class="{ 'justify-end': note.author === 'user' }"
      >
        <div
          class="note-bubble pa-3 rounded-lg position-relative"
          :class="note.author === 'user' ? 'note-user' : 'note-ai'"
          style="max-width: 80%"
        >
          <v-btn
            icon="mdi-close"
            size="x-small"
            variant="text"
            density="compact"
            class="delete-btn position-absolute"
            :loading="deletingNoteId === note.id"
            @click.stop="deleteNote(note.id)"
          />
          <div
            class="note-content markdown-content"
            v-html="renderMarkdown(note.note)"
          />
          <div
            class="note-meta text-caption mt-1"
            :class="note.author === 'user' ? 'text-right' : ''"
            :style="{ color: getMetaColor(note.author) }"
          >
            <span>{{ note.author === 'user' ? 'You' : 'AI' }}</span>
            <span class="mx-1">·</span>
            <span>{{ formatDateTime(note.created_at) }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="note-input pa-2 border-t">
      <v-textarea
        v-model="newNote"
        placeholder="Add a note... (Markdown supported)"
        variant="outlined"
        density="compact"
        rows="2"
        auto-grow
        hide-details
        :disabled="addingNote"
        @keydown.ctrl.enter="addNote('user')"
      />
      <div class="d-flex justify-end mt-2 ga-2">
        <v-btn
          type="button"
          size="small"
          variant="outlined"
          :loading="addingNote"
          :disabled="!newNote.trim()"
          @click="addNote('user')"
        >
          Add Note
        </v-btn>
      </div>
    </div>
  </div>
</template>

<style scoped>
.notes-container {
  min-height: 300px;
}

.notes-list {
  max-height: 400px;
}

.note-bubble {
  word-break: break-word;
}

.note-user {
  background-color: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));
}

.note-ai {
  background-color: rgb(var(--v-theme-surface-variant));
  color: rgb(var(--v-theme-on-surface-variant));
}

.delete-btn {
  top: 4px;
  right: 4px;
}

.markdown-content :deep(p) {
  margin-bottom: 0.25rem;
}

.markdown-content :deep(p:last-child) {
  margin-bottom: 0;
}

.markdown-content :deep(pre) {
  background-color: rgba(0, 0, 0, 0.1);
  padding: 0.5rem;
  border-radius: 4px;
  overflow-x: auto;
  font-size: 0.875rem;
}

.markdown-content :deep(code) {
  font-family: monospace;
  font-size: 0.875rem;
}

.markdown-content :deep(ul),
.markdown-content :deep(ol) {
  padding-left: 1.25rem;
  margin-bottom: 0.25rem;
}
</style>
