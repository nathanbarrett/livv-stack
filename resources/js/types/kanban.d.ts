export interface KanbanBoard {
  id: number
  user_id: number
  name: string
  description?: string | null
  project_name?: string | null
  columns?: KanbanColumn[]
  created_at?: string | null
  updated_at?: string | null
}

export interface KanbanColumn {
  id: number
  kanban_board_id: number
  name: string
  position: number
  color?: string | null
  tasks?: KanbanTask[]
  created_at?: string | null
  updated_at?: string | null
}

export interface KanbanTaskDependency {
  id: number
  title: string
  kanban_column_id: number
}

export type KanbanTaskNoteAuthor = 'user' | 'ai'

export interface KanbanTaskNote {
  id: number
  kanban_task_id: number
  note: string
  author: KanbanTaskNoteAuthor
  created_at: string
  updated_at: string
}

export interface KanbanTask {
  id: number
  kanban_column_id: number
  title: string
  description?: string | null
  implementation_plans?: string | null
  position: number
  due_date?: string | null
  priority?: 'low' | 'medium' | 'high' | null
  dependencies?: KanbanTaskDependency[]
  notes?: KanbanTaskNote[]
  created_at?: string | null
  updated_at?: string | null
}

export interface CreateBoardRequest {
  name: string
  description?: string
  project_name?: string
  copy_columns_from_board_id?: number
}

export interface UpdateBoardRequest {
  name?: string
  description?: string
  project_name?: string
}

export interface CreateColumnRequest {
  name: string
  color?: string
}

export interface UpdateColumnRequest {
  name?: string
  color?: string
}

export interface MoveColumnRequest {
  position: number
}

export interface CreateTaskRequest {
  title: string
  description?: string
  implementation_plans?: string
  due_date?: string
  priority?: 'low' | 'medium' | 'high'
  dependency_ids?: number[]
}

export interface UpdateTaskRequest {
  title?: string
  description?: string
  implementation_plans?: string
  due_date?: string | null
  priority?: 'low' | 'medium' | 'high' | null
  dependency_ids?: number[]
}

export interface CreateTaskNoteRequest {
  note: string
  author: KanbanTaskNoteAuthor
}

export interface UpdateTaskNoteRequest {
  note: string
}

export interface TaskNoteResponse {
  note: KanbanTaskNote
}

export interface TaskNotesResponse {
  notes: KanbanTaskNote[]
}

export interface MoveTaskRequest {
  kanban_column_id: number
  position: number
}

export interface BoardListResponse {
  boards: KanbanBoard[]
}

export interface BoardResponse {
  board: KanbanBoard
}

export interface ColumnResponse {
  column: KanbanColumn
}

export interface TaskResponse {
  task: KanbanTask
}

export interface BoardTasksResponse {
  tasks: KanbanTaskDependency[]
}

export interface KanbanBoardEvent {
  board_id: number
  action: 'created' | 'updated' | 'deleted' | 'moved'
  entity_type: 'board' | 'column' | 'task'
  entity_id: number | null
  timestamp: string
}
