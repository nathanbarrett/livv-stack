export interface KanbanTaskNote {
  // columns
  id: number
  kanban_task_id: number
  note: string
  author: KanbanTaskNoteAuthor
  created_at?: string | null
  updated_at?: string | null
  // relations
  task?: KanbanTask
  // counts
  // exists
  task_exists?: boolean
}

export interface AiChatSession {
  // columns
  id: number
  user_id: number
  title?: string | null
  model?: string | null
  settings?: Array<unknown> | null
  created_at?: string | null
  updated_at?: string | null
  // relations
  user?: User
  messages?: AiChatMessage[]
  latest_message?: AiChatMessage
  // counts
  messages_count?: number
  // exists
  user_exists?: boolean
  messages_exists?: boolean
  latest_message_exists?: boolean
}

export interface User {
  // columns
  id: number
  name: string
  email: string
  email_verified_at?: string | null
  password?: string
  remember_token?: string | null
  created_at?: string | null
  updated_at?: string | null
  // relations
  kanban_boards?: KanbanBoard[]
  chat_sessions?: AiChatSession[]
  memories?: UserMemory[]
  // counts
  kanban_boards_count?: number
  chat_sessions_count?: number
  memories_count?: number
  // exists
  kanban_boards_exists?: boolean
  chat_sessions_exists?: boolean
  memories_exists?: boolean
}

export interface KanbanTask {
  // columns
  id: number
  kanban_column_id: number
  title: string
  description?: string | null
  implementation_plans?: string | null
  position: number
  due_date?: string | null
  priority?: KanbanTaskPriority | null
  links?: Array<unknown> | null
  created_at?: string | null
  updated_at?: string | null
  deleted_at?: string | null
  // relations
  column?: KanbanColumn
  dependencies?: KanbanTask[]
  dependents?: KanbanTask[]
  notes?: KanbanTaskNote[]
  attachments?: KanbanTaskAttachment[]
  // counts
  dependencies_count?: number
  dependents_count?: number
  notes_count?: number
  attachments_count?: number
  // exists
  column_exists?: boolean
  dependencies_exists?: boolean
  dependents_exists?: boolean
  notes_exists?: boolean
  attachments_exists?: boolean
}

export interface KanbanBoard {
  // columns
  id: number
  user_id: number
  name: string
  description?: string | null
  project_name?: string | null
  created_at?: string | null
  updated_at?: string | null
  // relations
  user?: User
  columns?: KanbanColumn[]
  // counts
  columns_count?: number
  // exists
  user_exists?: boolean
  columns_exists?: boolean
}

export interface KanbanTaskAttachment {
  // columns
  id: number
  kanban_task_id: number
  filename: string
  original_filename: string
  mime_type: string
  size: number
  disk: string
  path: string
  created_at?: string | null
  updated_at?: string | null
  // mutators
  url: string
  // relations
  task?: KanbanTask
  // counts
  // exists
  task_exists?: boolean
}

export interface UserMemory {
  // columns
  id: number
  user_id: number
  type: string
  key: string
  value: string
  created_at?: string | null
  updated_at?: string | null
  // relations
  user?: User
  // counts
  // exists
  user_exists?: boolean
}

export interface AiChatMessage {
  // columns
  id: number
  ai_chat_session_id: number
  role: AiChatMessageRole
  content: string
  model?: string | null
  usage?: Array<unknown> | null
  metadata?: Array<unknown> | null
  created_at?: string | null
  updated_at?: string | null
  // relations
  session?: AiChatSession
  attachments?: AiChatAttachment[]
  // counts
  attachments_count?: number
  // exists
  session_exists?: boolean
  attachments_exists?: boolean
}

export interface AiChatAttachment {
  // columns
  id: number
  ai_chat_message_id?: number | null
  ai_chat_session_id?: number | null
  filename: string
  original_filename: string
  mime_type: string
  size: number
  disk: string
  path: string
  created_at?: string | null
  updated_at?: string | null
  // mutators
  url: string
  // relations
  message?: AiChatMessage
  session?: AiChatSession
  // counts
  // exists
  message_exists?: boolean
  session_exists?: boolean
}

export interface KanbanColumn {
  // columns
  id: number
  kanban_board_id: number
  name: string
  position: number
  color?: string | null
  description?: string | null
  created_at?: string | null
  updated_at?: string | null
  // relations
  board?: KanbanBoard
  tasks?: KanbanTask[]
  // counts
  tasks_count?: number
  // exists
  board_exists?: boolean
  tasks_exists?: boolean
}

const KanbanTaskNoteAuthor = {
  User: 'user',
  Ai: 'ai',
} as const;

export type KanbanTaskNoteAuthor = typeof KanbanTaskNoteAuthor[keyof typeof KanbanTaskNoteAuthor]

const KanbanTaskPriority = {
  Low: 'low',
  Medium: 'medium',
  High: 'high',
} as const;

export type KanbanTaskPriority = typeof KanbanTaskPriority[keyof typeof KanbanTaskPriority]

const AiChatMessageRole = {
  User: 'user',
  Assistant: 'assistant',
  System: 'system',
} as const;

export type AiChatMessageRole = typeof AiChatMessageRole[keyof typeof AiChatMessageRole]
