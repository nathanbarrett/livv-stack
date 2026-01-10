import { useEcho } from '@laravel/echo-vue'
import { watch, type Ref } from 'vue'
import type { KanbanBoardEvent } from '@js/types/kanban'

export function useKanbanBroadcast(
  boardId: Ref<number | null>,
  onUpdate: (event: KanbanBoardEvent) => void
) {
  let currentControls: ReturnType<typeof useEcho> | null = null

  watch(
    boardId,
    (newId, oldId) => {
      if (oldId && currentControls) {
        currentControls.leave()
        currentControls = null
      }

      if (newId) {
        currentControls = useEcho<KanbanBoardEvent>(
          `kanban.board.${newId}`,
          '.board.updated',
          onUpdate
        )
      }
    },
    { immediate: true }
  )

  return {
    leave: () => {
      if (currentControls) {
        currentControls.leave()
        currentControls = null
      }
    },
  }
}
