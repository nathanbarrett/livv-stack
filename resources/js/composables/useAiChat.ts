import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from '@js/common/axios'
import type {
  AiChatSession,
  AiChatMessage,
  AiChatAttachment,
  ProviderModelGroup,
  CreateSessionRequest,
  UpdateSessionRequest,
  SendMessageRequest,
  SessionListResponse,
  SessionResponse,
  AttachmentResponse,
} from '@js/types/chat'
import { error as showError } from '@js/common/snackbar'

interface SendMessageResponse {
  user_message: AiChatMessage
  assistant_message: AiChatMessage
}

export function useAiChat() {
  // State
  const sessions = ref<AiChatSession[]>([])
  const currentSession = ref<AiChatSession | null>(null)
  const isLoading = ref(false)
  const pendingAttachments = ref<AiChatAttachment[]>([])

  // Models from Inertia shared data
  const models = computed<ProviderModelGroup[]>(() => {
    const page = usePage()
    return (page.props.aiModels as ProviderModelGroup[]) || []
  })

  // Computed
  const sortedSessions = computed(() => {
    return [...sessions.value].sort((a, b) => {
      const dateA = new Date(a.updated_at || a.created_at || 0).getTime()
      const dateB = new Date(b.updated_at || b.created_at || 0).getTime()
      return dateB - dateA
    })
  })

  const messages = computed(() => currentSession.value?.messages || [])

  // Session Management
  async function loadSessions(): Promise<void> {
    try {
      isLoading.value = true
      const response = await axios.get<SessionListResponse>('/api/chat/sessions')
      sessions.value = response.data.sessions
    } catch (e) {
      showError('Failed to load sessions')
      console.error('Load sessions error:', e)
    } finally {
      isLoading.value = false
    }
  }

  async function loadSession(sessionId: number): Promise<void> {
    try {
      isLoading.value = true
      const response = await axios.get<SessionResponse>(`/api/chat/sessions/${sessionId}`)
      currentSession.value = response.data.session
    } catch (e) {
      showError('Failed to load session')
      console.error('Load session error:', e)
    } finally {
      isLoading.value = false
    }
  }

  async function createSession(data: CreateSessionRequest): Promise<AiChatSession | null> {
    try {
      isLoading.value = true
      const response = await axios.post<SessionResponse>('/api/chat/sessions', data)
      const session = response.data.session
      sessions.value.unshift(session)
      currentSession.value = session
      return session
    } catch (e) {
      showError('Failed to create session')
      console.error('Create session error:', e)
      return null
    } finally {
      isLoading.value = false
    }
  }

  async function updateSession(sessionId: number, data: UpdateSessionRequest): Promise<void> {
    try {
      const response = await axios.patch<SessionResponse>(`/api/chat/sessions/${sessionId}`, data)
      const updated = response.data.session

      // Update in sessions list
      const index = sessions.value.findIndex((s) => s.id === sessionId)
      if (index !== -1) {
        sessions.value[index] = { ...sessions.value[index], ...updated }
      }

      // Update current session if it's the one being updated
      if (currentSession.value?.id === sessionId) {
        currentSession.value = { ...currentSession.value, ...updated }
      }
    } catch (e) {
      showError('Failed to update session')
      console.error('Update session error:', e)
    }
  }

  async function deleteSession(sessionId: number): Promise<void> {
    try {
      await axios.delete(`/api/chat/sessions/${sessionId}`)
      sessions.value = sessions.value.filter((s) => s.id !== sessionId)

      if (currentSession.value?.id === sessionId) {
        currentSession.value = null
      }
    } catch (e) {
      showError('Failed to delete session')
      console.error('Delete session error:', e)
    }
  }

  async function clearSession(sessionId: number): Promise<void> {
    try {
      const response = await axios.post<SessionResponse>(`/api/chat/sessions/${sessionId}/clear`)
      const cleared = response.data.session

      if (currentSession.value?.id === sessionId) {
        currentSession.value = { ...currentSession.value, ...cleared, messages: [] }
      }

      // Update title in sessions list
      const index = sessions.value.findIndex((s) => s.id === sessionId)
      if (index !== -1) {
        sessions.value[index] = { ...sessions.value[index], title: cleared.title }
      }
    } catch (e) {
      showError('Failed to clear session')
      console.error('Clear session error:', e)
    }
  }

  // Message Management
  async function sendMessage(content: string): Promise<void> {
    if (!currentSession.value) return

    // Create optimistic user message immediately
    const tempUserMessage: AiChatMessage = {
      id: -Date.now(), // Temporary negative ID
      ai_chat_session_id: currentSession.value.id,
      role: 'user',
      content,
      created_at: new Date().toISOString(),
      attachments: [...pendingAttachments.value],
    }

    // Add user message to UI immediately
    if (currentSession.value.messages) {
      currentSession.value.messages.push(tempUserMessage)
    } else {
      currentSession.value.messages = [tempUserMessage]
    }

    // Clear pending attachments from UI
    const attachmentIds = pendingAttachments.value.map((a) => a.id)
    pendingAttachments.value = []

    try {
      isLoading.value = true

      const data: SendMessageRequest = {
        content,
        attachment_ids: attachmentIds,
      }

      const response = await axios.post<SendMessageResponse>(
        `/api/chat/sessions/${currentSession.value.id}/messages`,
        data
      )

      const { user_message, assistant_message } = response.data

      // Replace temp user message with real one and add assistant message
      if (currentSession.value.messages) {
        const tempIndex = currentSession.value.messages.findIndex((m) => m.id === tempUserMessage.id)
        if (tempIndex !== -1) {
          currentSession.value.messages[tempIndex] = user_message
        }
        currentSession.value.messages.push(assistant_message)
      }

      // Update session in sidebar (title may have been generated)
      const sessionIndex = sessions.value.findIndex((s) => s.id === currentSession.value?.id)
      if (sessionIndex !== -1) {
        // Reload session to get updated title
        const sessionResponse = await axios.get<SessionResponse>(
          `/api/chat/sessions/${currentSession.value.id}`
        )
        const updatedSession = sessionResponse.data.session
        sessions.value[sessionIndex] = {
          ...sessions.value[sessionIndex],
          title: updatedSession.title,
          updated_at: updatedSession.updated_at,
        }
      }
    } catch (e) {
      showError('Failed to send message')
      console.error('Send message error:', e)
      // Remove the optimistic message on error
      if (currentSession.value?.messages) {
        currentSession.value.messages = currentSession.value.messages.filter(
          (m) => m.id !== tempUserMessage.id
        )
      }
    } finally {
      isLoading.value = false
    }
  }

  async function deleteMessage(messageId: number): Promise<void> {
    if (!currentSession.value?.messages) return

    try {
      await axios.delete(`/api/chat/messages/${messageId}`)
      currentSession.value.messages = currentSession.value.messages.filter((m) => m.id !== messageId)
    } catch (e) {
      showError('Failed to delete message')
      console.error('Delete message error:', e)
    }
  }

  // Attachment Management
  async function uploadAttachment(file: File): Promise<AiChatAttachment | null> {
    if (!currentSession.value) return null

    try {
      const formData = new FormData()
      formData.append('file', file)
      formData.append('session_id', String(currentSession.value.id))

      const response = await axios.post<AttachmentResponse>('/api/chat/attachments', formData)
      const attachment = response.data.attachment
      pendingAttachments.value.push(attachment)
      return attachment
    } catch (e) {
      showError('Failed to upload file')
      console.error('Upload attachment error:', e)
      return null
    }
  }

  function removePendingAttachment(attachmentId: number): void {
    pendingAttachments.value = pendingAttachments.value.filter((a) => a.id !== attachmentId)
    // Optionally delete from server too
    axios.delete(`/api/chat/attachments/${attachmentId}`).catch(console.error)
  }

  // Session switching helper
  function selectSession(session: AiChatSession): void {
    if (currentSession.value?.id !== session.id) {
      loadSession(session.id)
    }
  }

  return {
    // State
    sessions,
    currentSession,
    models,
    isLoading,
    pendingAttachments,

    // Computed
    sortedSessions,
    messages,

    // Session methods
    loadSessions,
    loadSession,
    createSession,
    updateSession,
    deleteSession,
    clearSession,
    selectSession,

    // Message methods
    sendMessage,
    deleteMessage,

    // Attachment methods
    uploadAttachment,
    removePendingAttachment,
  }
}
