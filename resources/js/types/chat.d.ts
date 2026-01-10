export type AiChatMessageRole = 'user' | 'assistant' | 'system'

export interface AiChatSession {
  id: number
  user_id: number
  title?: string | null
  model?: string | null
  settings?: AiChatSettings | null
  messages?: AiChatMessage[]
  created_at?: string | null
  updated_at?: string | null
}

export interface AiChatMessage {
  id: number
  ai_chat_session_id: number
  role: AiChatMessageRole
  content: string
  model?: string | null
  usage?: AiChatUsage | null
  metadata?: Record<string, unknown> | null
  attachments?: AiChatAttachment[]
  created_at?: string | null
  updated_at?: string | null
}

export interface AiChatAttachment {
  id: number
  ai_chat_message_id?: number | null
  ai_chat_session_id?: number | null
  original_filename: string
  mime_type: string
  size: number
  url?: string
}

export interface AiChatSettings {
  system_prompt?: string
  temperature?: number
  max_tokens?: number
}

export interface AiChatUsage {
  prompt_tokens: number
  completion_tokens: number
}

export interface ProviderModelGroup {
  provider: string
  providerLabel: string
  enabled: boolean
  models: ProviderModelOption[]
}

export interface ProviderModelOption {
  value: string
  label: string
}

// API Request Types
export interface CreateSessionRequest {
  title?: string
  model: string
  settings?: AiChatSettings
}

export interface UpdateSessionRequest {
  title?: string
  model?: string
  settings?: AiChatSettings
}

export interface SendMessageRequest {
  content: string
  attachment_ids?: number[]
}

export interface UploadAttachmentRequest {
  file: File
  session_id: number
}

// API Response Types
export interface SessionListResponse {
  sessions: AiChatSession[]
}

export interface SessionResponse {
  session: AiChatSession
}

export interface MessageResponse {
  message: AiChatMessage
}

export interface AttachmentResponse {
  attachment: AiChatAttachment
}

export interface ModelsResponse {
  models: ProviderModelGroup[]
}

// User Memory Types
export interface UserMemory {
  id: number
  user_id: number
  type: string
  key: string
  value: string
  created_at?: string | null
  updated_at?: string | null
}

export interface MemoriesResponse {
  memories: UserMemory[]
}

export interface MemoryResponse {
  memory: UserMemory
}

