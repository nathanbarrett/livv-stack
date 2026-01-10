import type { Directive, DirectiveBinding } from 'vue'

interface FillRemainingHeightOptions {
  offset?: number
  minHeight?: number
}

function calculateAndSetHeight(el: HTMLElement, options: FillRemainingHeightOptions): void {
  const rect = el.getBoundingClientRect()
  const windowHeight = window.innerHeight
  const offset = options.offset ?? 0
  const minHeight = options.minHeight ?? 0

  const remainingHeight = windowHeight - rect.top - offset
  const finalHeight = Math.max(remainingHeight, minHeight)

  el.style.height = `${finalHeight}px`
}

function parseOptions(binding: DirectiveBinding): FillRemainingHeightOptions {
  if (typeof binding.value === 'number') {
    return { offset: binding.value }
  }
  if (typeof binding.value === 'object' && binding.value !== null) {
    return binding.value as FillRemainingHeightOptions
  }
  return {}
}

const resizeHandlers = new WeakMap<HTMLElement, () => void>()

export const vFillRemainingHeight: Directive<HTMLElement, FillRemainingHeightOptions | number | undefined> = {
  mounted(el, binding) {
    const options = parseOptions(binding)

    const handleResize = (): void => {
      calculateAndSetHeight(el, options)
    }

    // Store handler for cleanup
    resizeHandlers.set(el, handleResize)

    // Initial calculation
    handleResize()

    // Listen for resize
    window.addEventListener('resize', handleResize)
  },

  updated(el, binding) {
    const options = parseOptions(binding)
    calculateAndSetHeight(el, options)
  },

  unmounted(el) {
    const handleResize = resizeHandlers.get(el)
    if (handleResize) {
      window.removeEventListener('resize', handleResize)
      resizeHandlers.delete(el)
    }
  },
}
