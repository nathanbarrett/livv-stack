import { ref, onMounted } from 'vue'

const STORAGE_KEY = 'ai-chat-sound-muted'

export function useNotificationSound() {
  const isMuted = ref(false)
  const audio = ref<HTMLAudioElement | null>(null)

  onMounted(() => {
    // Load muted state from localStorage
    const stored = localStorage.getItem(STORAGE_KEY)
    isMuted.value = stored === 'true'

    // Preload audio
    audio.value = new Audio('/sounds/notification-457196.mp3')
    audio.value.volume = 0.5
  })

  function toggleMute(): void {
    isMuted.value = !isMuted.value
    localStorage.setItem(STORAGE_KEY, String(isMuted.value))
  }

  function playSound(): void {
    if (isMuted.value || !audio.value) return

    // Reset and play
    audio.value.currentTime = 0
    audio.value.play().catch(() => {
      // Ignore autoplay errors (user hasn't interacted with page yet)
    })
  }

  return {
    isMuted,
    toggleMute,
    playSound,
  }
}
