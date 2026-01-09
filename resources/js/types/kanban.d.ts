export interface KanbanBoard {
  id: number
  user_id: number
  name: string
  description?: string | null
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

export interface KanbanTask {
  id: number
  kanban_column_id: number
  title: string
  description?: string | null
  position: number
  due_date?: string | null
  priority?: 'low' | 'medium' | 'high' | null
  created_at?: string | null
  updated_at?: string | null
}

export interface CreateBoardRequest {
  name: string
  description?: string
}

export interface UpdateBoardRequest {
  name?: string
  description?: string
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
  due_date?: string
  priority?: 'low' | 'medium' | 'high'
}

export interface UpdateTaskRequest {
  title?: string
  description?: string
  due_date?: string
  priority?: 'low' | 'medium' | 'high'
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
